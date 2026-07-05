<?php
function handle_update(&$result, $source, $query, $cfg, $key) {
    @session_start();
    if (empty($_SESSION['admin']) || ($_SESSION['role'] ?? '') !== 'admin') {
        $result['error'] = 'permission denied';
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $result['error'] = 'POST required';
        return;
    }

    $input = updateJsonDecode(file_get_contents('php://input'));
    if (!is_array($input)) $input = [];

    $root = realpath(__DIR__ . '/..');
    if (!$root || !is_dir($root)) {
        $result['error'] = 'site root not found';
        return;
    }

    $options = updateOptions($cfg, $root);
    if (!$options['endpoint']) {
        $result['error'] = 'missing config: up.json endpoint';
        return;
    }

    $release = updateFetchManifest($options, $root);
    if (!$release['success']) {
        $result['error'] = $release['error'];
        return;
    }
    $manifest = $release['data'];

    if ($source === 'update_check') {
        $result['success'] = true;
        $result['data'] = updateManifestSummary($manifest, false);
        return;
    }

    if ($source !== 'update_apply') return;

    if (empty($input['confirmed'])) {
        $result['error'] = 'check and confirm update before apply';
        return;
    }
    if (!empty($input['expected_version']) && ($manifest['version'] ?? '') !== $input['expected_version']) {
        $result['error'] = 'update manifest changed; please check again';
        return;
    }
    if (!empty($input['expected_sha256']) && strtolower($manifest['sha256'] ?? '') !== strtolower($input['expected_sha256'])) {
        $result['error'] = 'update package changed; please check again';
        return;
    }

    $expectedSha = strtolower($manifest['sha256'] ?? '');
    if (!preg_match('/^[a-f0-9]{64}$/', $expectedSha)) {
        $result['error'] = 'invalid sha256 from update endpoint';
        return;
    }

    $releaseDir = $root . DIRECTORY_SEPARATOR . 'release_updates';
    if (!is_dir($releaseDir) && !@mkdir($releaseDir, 0755, true)) {
        $result['error'] = 'cannot create release_updates directory';
        return;
    }

    $fileName = updateSafeFileName($manifest['package_name'] ?? ('update_' . ($manifest['version'] ?? 'latest') . '.zip'));
    $downloadPath = $releaseDir . DIRECTORY_SEPARATOR . date('Ymd_His') . '_' . $fileName;
    $download = updateDownloadFile($manifest['package_url'], $downloadPath, $options);
    if (!$download['success']) {
        @unlink($downloadPath);
        $result['error'] = $download['error'];
        return;
    }

    $actualSha = hash_file('sha256', $downloadPath);
    if (!hash_equals($expectedSha, strtolower($actualSha))) {
        @unlink($downloadPath);
        $result['error'] = 'sha256 mismatch; package removed';
        return;
    }

    if (filesize($downloadPath) < 22 || updateFileHeader($downloadPath, 4) !== "PK\x03\x04") {
        @unlink($downloadPath);
        $result['error'] = 'downloaded file is not a valid zip package';
        return;
    }

    $plan = updateBuildZipPlan($downloadPath, $root, $options, !empty($input['allow_same_version']), $manifest['version'] ?? '');
    if (!$plan['success']) {
        $result['error'] = $plan['error'];
        return;
    }

    $applied = updateApplyZipPlan($downloadPath, $root, $plan['data'], $releaseDir);
    if (!$applied['success']) {
        $result['error'] = $applied['error'];
        $result['data'] = $applied['data'] ?? [];
        return;
    }

    $version = null;
    $versionFile = $root . DIRECTORY_SEPARATOR . 'version.json';
    if (is_file($versionFile)) {
        $version = updateReadJsonFile($versionFile);
    }

    $result['success'] = true;
    $result['data'] = array_merge(updateManifestSummary($manifest, true), [
        'saved_as' => 'release_updates/' . basename($downloadPath),
        'size' => filesize($downloadPath),
        'sha256' => $actualSha,
        'backup_dir' => $applied['backup_dir'],
        'applied' => $applied['applied'],
        'skipped' => $plan['data']['skipped'],
        'version' => $version,
    ]);
}

function updateOptions($cfg, $root) {
    $update = is_array($cfg['update'] ?? null) ? $cfg['update'] : [];
    $upFile = $root . DIRECTORY_SEPARATOR . 'up.json';
    if (is_file($upFile)) {
        $up = updateReadJsonFile($upFile);
        if (is_array($up)) {
            $update = is_array($up['update'] ?? null) ? $up['update'] : $up;
        }
    }
    $endpoint = trim($update['endpoint'] ?? '');
    return [
        'endpoint' => updateNormalizeHttpsUrl($endpoint),
        'endpoint_host' => strtolower(parse_url($endpoint, PHP_URL_HOST) ?: ''),
        'app' => trim($update['app'] ?? ($cfg['update_app'] ?? 'cx_toolbox')),
        'channel' => trim($update['channel'] ?? 'stable'),
        'public_key' => trim($update['public_key'] ?? ''),
        'allow_unsigned' => !empty($update['allow_unsigned']),
        'max_package_bytes' => max(1024 * 1024, intval($update['max_package_mb'] ?? 80) * 1024 * 1024),
        'max_extracted_bytes' => max(1024 * 1024, intval($update['max_extracted_mb'] ?? 250) * 1024 * 1024),
        'keep_local' => !array_key_exists('keep_local', $update) || (bool)$update['keep_local'],
    ];
}

function updateNormalizeHttpsUrl($url) {
    $url = trim($url);
    if ($url === '') return '';
    $parts = parse_url($url);
    if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) return '';
    return $url;
}

function updateFetchManifest($options, $root) {
    $currentVersion = updateCurrentVersion($root);

    $sep = strpos($options['endpoint'], '?') === false ? '?' : '&';
    $url = $options['endpoint'] . $sep . http_build_query([
        'application' => $options['app'],
        'channel' => $options['channel'],
        'current_version' => $currentVersion,
        'domain' => updateCurrentDomain(),
    ]);
    $manifest = updateHttpJson($url, $options);
    if (!$manifest || !is_array($manifest)) {
        return ['success' => false, 'error' => 'cannot read update manifest'];
    }
    if (isset($manifest['success']) && !$manifest['success']) {
        return ['success' => false, 'error' => $manifest['error'] ?? 'no update available'];
    }
    $valid = updateValidateManifest($manifest, $options);
    if (!$valid['success']) return $valid;
    $versionCheck = updateCheckManifestVersion($currentVersion, $manifest['version'] ?? '');
    if (!$versionCheck['success']) return $versionCheck;
    return ['success' => true, 'data' => $manifest];
}

function updateCurrentVersion($root) {
    $versionFile = $root . DIRECTORY_SEPARATOR . 'version.json';
    if (!is_file($versionFile)) return '';
    $version = updateReadJsonFile($versionFile);
    return is_array($version) ? trim((string)($version['version'] ?? '')) : '';
}

function updateCurrentDomain() {
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    $host = strtolower(trim((string)$host));
    $host = preg_replace('/:\d+$/', '', $host);
    return $host;
}

function updateValidateManifest($manifest, $options) {
    foreach (['app', 'version', 'channel', 'package_url', 'sha256'] as $field) {
        if (empty($manifest[$field]) || !is_string($manifest[$field])) {
            return ['success' => false, 'error' => 'manifest missing field: ' . $field];
        }
    }
    if ($manifest['app'] !== $options['app']) return ['success' => false, 'error' => 'manifest app mismatch'];
    if ($manifest['channel'] !== $options['channel']) return ['success' => false, 'error' => 'manifest channel mismatch'];
    if (!preg_match('/^[a-f0-9]{64}$/i', $manifest['sha256'])) return ['success' => false, 'error' => 'manifest sha256 is invalid'];
    if (!updateIsTrustedPackageUrl($manifest['package_url'], $options)) return ['success' => false, 'error' => 'package url host mismatch'];

    if ($options['public_key'] === '' && !$options['allow_unsigned']) {
        return ['success' => false, 'error' => 'missing config: update.public_key'];
    }
    if ($options['public_key'] !== '') {
        if (empty($manifest['signature'])) return ['success' => false, 'error' => 'manifest missing signature'];
        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            return ['success' => false, 'error' => 'sodium extension required for signature verification'];
        }
        $message = updateSignatureMessage($manifest);
        $sig = base64_decode($manifest['signature'], true);
        $pub = base64_decode($options['public_key'], true);
        if (!$sig || !$pub || !sodium_crypto_sign_verify_detached($sig, $message, $pub)) {
            return ['success' => false, 'error' => 'manifest signature verification failed'];
        }
    }

    return ['success' => true];
}

function updateSignatureMessage($manifest) {
    return implode('|', [
        $manifest['app'] ?? '',
        $manifest['version'] ?? '',
        $manifest['channel'] ?? '',
        strtolower($manifest['sha256'] ?? ''),
    ]);
}

function updateManifestSummary($manifest, $includeUrl) {
    $summary = [
        'app' => $manifest['app'],
        'version' => $manifest['version'],
        'channel' => $manifest['channel'],
        'package_name' => $manifest['package_name'] ?? basename(parse_url($manifest['package_url'], PHP_URL_PATH) ?: 'update.zip'),
        'package_size' => intval($manifest['size'] ?? 0),
        'sha256' => strtolower($manifest['sha256']),
        'published_at' => $manifest['published_at'] ?? '',
        'changelog' => $manifest['changelog'] ?? '',
        'has_signature' => !empty($manifest['signature']),
        'current_version' => $manifest['current_version'] ?? '',
        'latest_version' => $manifest['latest_version'] ?? ($manifest['version'] ?? ''),
        'step_update' => !empty($manifest['step_update']),
    ];
    if (isset($manifest['authorization']) && is_array($manifest['authorization'])) {
        $summary['authorization'] = updateNormalizeAuthorization($manifest['authorization']);
    }
    if ($includeUrl) $summary['package_url'] = $manifest['package_url'];
    return $summary;
}

function updateNormalizeAuthorization($auth) {
    return [
        'domain' => (string)($auth['domain'] ?? ''),
        'qq' => (string)($auth['qq'] ?? ''),
        'expires_at' => (string)($auth['expires_at'] ?? ''),
        'status' => (string)($auth['status'] ?? ''),
        'days_left' => isset($auth['days_left']) ? $auth['days_left'] : null,
        'permanent' => !empty($auth['permanent']),
        'banned' => !empty($auth['banned']),
    ];
}

function updateHttpJson($url, $options) {
    $body = updateHttpGet($url, 30, $options, ['Accept: application/json']);
    if ($body === false || $body === '') return null;
    return updateJsonDecode($body);
}

function updateReadJsonFile($file) {
    $raw = @file_get_contents($file);
    if ($raw === false) return null;
    return updateJsonDecode($raw);
}

function updateJsonDecode($raw) {
    if (!is_string($raw)) return null;
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    $json = json_decode($raw, true);
    return is_array($json) ? $json : null;
}

function updateHttpGet($url, $timeout, $options, $headers = []) {
    if (!function_exists('curl_init')) return false;
    $url = updateFollowTrustedRedirects($url, $timeout, $options);
    if (!$url) return false;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => 'cx-toolbox-updater',
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 300) ? $res : false;
}

function updateDownloadFile($url, $target, $options) {
    if (!function_exists('curl_init')) {
        return ['success' => false, 'error' => 'curl extension required for secure download'];
    }
    $url = updateFollowTrustedRedirects($url, 30, $options);
    if (!$url) return ['success' => false, 'error' => 'package url is not trusted'];

    $fp = @fopen($target, 'wb');
    if (!$fp) return ['success' => false, 'error' => 'cannot write package file'];

    $tooLarge = false;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => 'cx-toolbox-updater',
        CURLOPT_HTTPHEADER => ['Accept: application/octet-stream'],
        CURLOPT_NOPROGRESS => false,
    ]);
    $progress = function($ch, $downloadTotal, $downloaded, $uploadTotal = 0, $uploaded = 0) use ($options, &$tooLarge) {
        if (($downloadTotal > 0 && $downloadTotal > $options['max_package_bytes']) || $downloaded > $options['max_package_bytes']) {
            $tooLarge = true;
            return 1;
        }
        return 0;
    };
    if (defined('CURLOPT_XFERINFOFUNCTION')) curl_setopt($ch, CURLOPT_XFERINFOFUNCTION, $progress);
    elseif (defined('CURLOPT_PROGRESSFUNCTION')) curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progress);

    $ok = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    fclose($fp);

    if ($tooLarge) return ['success' => false, 'error' => 'package exceeds size limit'];
    if (!$ok || $code < 200 || $code >= 300) return ['success' => false, 'error' => 'download failed: ' . ($err ?: 'HTTP ' . $code)];
    return ['success' => true];
}

function updateFollowTrustedRedirects($url, $timeout, $options) {
    for ($i = 0; $i < 5; $i++) {
        if (!updateIsTrustedPackageUrl($url, $options)) return false;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT => 'cx-toolbox-updater',
        ]);
        $headers = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 300 && $code < 400 && preg_match('/^Location:\s*(.+)$/im', (string)$headers, $m)) {
            $next = trim($m[1]);
            if (strpos($next, '/') === 0) {
                $p = parse_url($url);
                $next = $p['scheme'] . '://' . $p['host'] . $next;
            }
            $url = $next;
            continue;
        }
        return updateIsTrustedPackageUrl($url, $options) ? $url : false;
    }
    return false;
}

function updateIsTrustedPackageUrl($url, $options) {
    $p = parse_url($url);
    if (!$p || strtolower($p['scheme'] ?? '') !== 'https') return false;
    return strtolower($p['host'] ?? '') === $options['endpoint_host'];
}

function updateBuildZipPlan($zipFile, $root, $options, $allowSameVersion, $expectedVersion) {
    if (!class_exists('ZipArchive')) {
        return ['success' => false, 'error' => 'ZipArchive extension required'];
    }
    $zip = new ZipArchive();
    if ($zip->open($zipFile) !== true) return ['success' => false, 'error' => 'cannot open zip package'];

    $prefix = updateDetectZipPrefix($zip);
    $files = [];
    $skipped = [];
    $total = 0;
    $newVersion = null;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
        $rel = updateNormalizeEntry($entry, $prefix);
        if ($rel === '' || substr($entry, -1) === '/') continue;
        if (!updateIsSafeRelativePath($rel)) {
            $zip->close();
            return ['success' => false, 'error' => 'zip contains unsafe path: ' . $entry];
        }
        if (updateZipEntryIsSymlink($zip, $i)) {
            $zip->close();
            return ['success' => false, 'error' => 'zip contains symlink: ' . $entry];
        }
        $stat = $zip->statIndex($i);
        $size = intval($stat['size'] ?? 0);
        $total += $size;
        if ($total > $options['max_extracted_bytes']) {
            $zip->close();
            return ['success' => false, 'error' => 'extracted size exceeds limit'];
        }
        if (updateShouldSkip($rel, $options['keep_local'])) {
            $skipped[] = $rel;
            continue;
        }
        if ($rel === 'version.json') {
            $json = $zip->getFromIndex($i);
            $versionData = updateJsonDecode($json ?: '');
            $newVersion = $versionData['version'] ?? null;
        }
        $files[] = ['entry' => $entry, 'rel' => $rel, 'size' => $size];
    }
    $zip->close();

    if (!$files) return ['success' => false, 'error' => 'zip contains no applicable files'];
    if (!$newVersion) return ['success' => false, 'error' => 'package missing version.json'];
    if ($expectedVersion !== '' && $newVersion !== $expectedVersion) {
        return ['success' => false, 'error' => 'package version mismatch: ' . $newVersion . ' != ' . $expectedVersion];
    }
    $versionCheck = updateCheckVersionIncrease($root, $newVersion, $allowSameVersion);
    if (!$versionCheck['success']) return $versionCheck;

    return ['success' => true, 'data' => ['files' => $files, 'skipped' => $skipped, 'new_version' => $newVersion, 'total_size' => $total]];
}

function updateApplyZipPlan($zipFile, $root, $plan, $releaseDir) {
    $backupDir = $releaseDir . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . 'update_' . date('Ymd_His');
    if (!is_dir($backupDir) && !@mkdir($backupDir, 0755, true)) return ['success' => false, 'error' => 'cannot create backup directory'];

    $manifest = ['new_files' => [], 'backed_up' => []];
    foreach ($plan['files'] as $file) {
        $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file['rel']);
        if (is_link($target)) return ['success' => false, 'error' => 'target is a symlink: ' . $file['rel']];
        if (!updateIsPathInside($target, $root)) return ['success' => false, 'error' => 'target path escapes root: ' . $file['rel']];
        if (is_file($target)) {
            $backup = $backupDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file['rel']);
            $dir = dirname($backup);
            if (!is_dir($dir) && !@mkdir($dir, 0755, true)) return ['success' => false, 'error' => 'cannot create backup subdirectory'];
            if (!@copy($target, $backup)) return ['success' => false, 'error' => 'backup failed: ' . $file['rel']];
            $manifest['backed_up'][] = $file['rel'];
        } else {
            $manifest['new_files'][] = $file['rel'];
        }
    }
    file_put_contents($backupDir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $zip = new ZipArchive();
    if ($zip->open($zipFile) !== true) return ['success' => false, 'error' => 'cannot reopen zip package'];
    $applied = [];
    foreach ($plan['files'] as $file) {
        $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file['rel']);
        $dir = dirname($target);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            $zip->close();
            updateRollback($root, $backupDir, $manifest);
            return ['success' => false, 'error' => 'mkdir failed; rolled back: ' . $file['rel'], 'data' => ['backup_dir' => $backupDir]];
        }
        $in = $zip->getStream($file['entry']);
        $out = @fopen($target, 'wb');
        if (!$in || !$out) {
            if ($in) fclose($in);
            if ($out) fclose($out);
            $zip->close();
            updateRollback($root, $backupDir, $manifest);
            return ['success' => false, 'error' => 'write failed; rolled back: ' . $file['rel'], 'data' => ['backup_dir' => $backupDir]];
        }
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);
        $applied[] = $file['rel'];
    }
    $zip->close();

    return ['success' => true, 'backup_dir' => str_replace('\\', '/', substr($backupDir, strlen($root) + 1)), 'applied' => $applied];
}

function updateRollback($root, $backupDir, $manifest) {
    foreach (($manifest['new_files'] ?? []) as $rel) {
        $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (is_file($target) && updateIsPathInside($target, $root)) @unlink($target);
    }
    foreach (($manifest['backed_up'] ?? []) as $rel) {
        $backup = $backupDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (is_file($backup) && updateIsPathInside($target, $root)) {
            $dir = dirname($target);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            @copy($backup, $target);
        }
    }
}

function updateCheckVersionIncrease($root, $newVersion, $allowSameVersion) {
    if (!$newVersion || $allowSameVersion) return ['success' => true];
    return updateCheckManifestVersion(updateCurrentVersion($root), $newVersion, 'package version is not newer');
}

function updateCheckManifestVersion($currentVersion, $newVersion, $message = 'remote version is not newer') {
    $currentVersion = trim((string)$currentVersion);
    $newVersion = trim((string)$newVersion);
    if ($newVersion === '') return ['success' => false, 'error' => 'missing remote version'];
    if ($currentVersion === '') return ['success' => true];
    if (preg_match('/^\d+(?:\.\d+){1,3}$/', $currentVersion) && preg_match('/^\d+(?:\.\d+){1,3}$/', $newVersion)) {
        if (version_compare($newVersion, $currentVersion, '<=')) {
            return ['success' => false, 'error' => $message . ': ' . $newVersion . ' <= ' . $currentVersion];
        }
    }
    return ['success' => true];
}

function updateZipEntryIsSymlink($zip, $index) {
    if (!method_exists($zip, 'getExternalAttributesIndex')) return false;
    $opsys = 0;
    $attr = 0;
    if (!$zip->getExternalAttributesIndex($index, $opsys, $attr)) return false;
    return ((($attr >> 16) & 0170000) === 0120000);
}

function updateDetectZipPrefix($zip) {
    $prefix = null;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = str_replace('\\', '/', $zip->getNameIndex($i));
        if ($name === '' || substr($name, -1) === '/') continue;
        $parts = explode('/', $name, 2);
        if (count($parts) < 2) return '';
        if ($prefix === null) $prefix = $parts[0];
        elseif ($prefix !== $parts[0]) return '';
    }
    return $prefix ?: '';
}

function updateNormalizeEntry($entry, $prefix) {
    $rel = ltrim(str_replace('\\', '/', $entry), '/');
    if ($prefix !== '' && strpos($rel, $prefix . '/') === 0) $rel = substr($rel, strlen($prefix) + 1);
    return $rel;
}

function updateIsSafeRelativePath($rel) {
    if ($rel === '' || strpos($rel, "\0") !== false) return false;
    if ($rel[0] === '/' || preg_match('/^[A-Za-z]:/', $rel)) return false;
    foreach (explode('/', str_replace('\\', '/', $rel)) as $part) {
        if ($part === '..') return false;
    }
    return true;
}

function updateShouldSkip($rel, $keepLocal) {
    $rel = ltrim(str_replace('\\', '/', $rel), '/');
    foreach (['.git/', 'release_updates/'] as $prefix) {
        if (strpos($rel, $prefix) === 0) return true;
    }
    if (!$keepLocal) return false;
    $protectedFiles = ['config.json', 'up.json', 'config.php', '.env', 'install/install.lock', 'error.log', 'api/oauth/Oauth.config.php'];
    if (in_array($rel, $protectedFiles, true)) return true;
    foreach (['uploads/', 'cache/', 'runtime/', 'storage/'] as $prefix) {
        if (strpos($rel, $prefix) === 0) return true;
    }
    return preg_match('/\.(db|sqlite|sqlite3)$/i', $rel) === 1;
}

function updateIsPathInside($target, $root) {
    $root = realpath($root);
    if (!$root) return false;
    $dir = dirname($target);
    while (!is_dir($dir) && dirname($dir) !== $dir) $dir = dirname($dir);
    $dir = realpath($dir);
    if (!$dir) return false;
    $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    return strpos($dir, $root) === 0;
}

function updateSafeFileName($name) {
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
    $name = trim($name, '._');
    if ($name === '') return 'update.zip';
    if (substr(strtolower($name), -4) !== '.zip') $name .= '.zip';
    return $name;
}

function updateFileHeader($file, $len) {
    $fp = @fopen($file, 'rb');
    if (!$fp) return '';
    $data = fread($fp, $len);
    fclose($fp);
    return $data;
}
