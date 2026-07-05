<?php
function handle_plugin(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'plugin_list':
            $result['success'] = true;
            $result['data'] = scanPlugins();
            return;

        case 'plugin_delete':
            if (!pluginRequireAdmin($result)) return;
            $name = basename($_GET['name'] ?? '');
            if ($name === '') { $result['error'] = '缺少插件名称'; return; }
            $pluginDir = __DIR__ . '/../plugins/' . $name;
            if (!is_dir($pluginDir)) { $result['error'] = '插件不存在'; return; }
            if (!deleteDir($pluginDir)) { $result['error'] = '删除失败'; return; }
            $result['success'] = true;
            $result['data'] = ['deleted' => $name];
            return;

        case 'plugin_install':
            if (!pluginRequireAdmin($result)) return;
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
                $result['error'] = '请上传插件 ZIP 包';
                return;
            }
            $installed = pluginInstallUploaded($_FILES['file']);
            if (!$installed['success']) { $result['error'] = $installed['error']; return; }
            $result['success'] = true;
            $result['data'] = $installed['data'];
            return;

        case 'plugin_center_list':
            if (!pluginRequireAdmin($result)) return;
            $listed = pluginCenterFetchList($cfg);
            if (!$listed['success']) { $result['error'] = $listed['error']; return; }
            $installed = pluginInstalledNames();
            foreach ($listed['data'] as &$plugin) {
                $plugin['installed'] = in_array($plugin['plugin_id'] ?? '', $installed, true);
            }
            unset($plugin);
            $result['success'] = true;
            $result['data'] = $listed['data'];
            if (isset($listed['authorization'])) $result['authorization'] = $listed['authorization'];
            return;

        case 'plugin_center_install':
            if (!pluginRequireAdmin($result)) return;
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '必须使用 POST 请求'; return; }
            $input = pluginJsonDecode(file_get_contents('php://input'));
            if (!is_array($input)) $input = [];
            $pluginId = trim($input['id'] ?? '');
            if ($pluginId === '') { $result['error'] = '缺少插件编号'; return; }
            $installed = pluginCenterInstall($cfg, $pluginId);
            if (!$installed['success']) { $result['error'] = $installed['error']; return; }
            $result['success'] = true;
            $result['data'] = $installed['data'];
            return;
    }
}

function pluginRequireAdmin(&$result) {
    @session_start();
    if (empty($_SESSION['admin']) || ($_SESSION['role'] ?? '') !== 'admin') {
        $result['error'] = '没有管理员权限';
        return false;
    }
    return true;
}

function pluginInstallUploaded($file) {
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => pluginUploadError($file['error'] ?? -1)];
    }
    if (strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION)) !== 'zip') {
        return ['success' => false, 'error' => '只支持 ZIP 插件包'];
    }
    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > 50 * 1024 * 1024) {
        return ['success' => false, 'error' => '插件包大小不正确或超过 50MB'];
    }
    if (pluginFileHeader($file['tmp_name'], 4) !== "PK\x03\x04") {
        return ['success' => false, 'error' => '插件包必须是 ZIP 文件'];
    }
    $fallback = pathinfo($file['name'] ?? 'plugin', PATHINFO_FILENAME);
    return pluginInstallZip($file['tmp_name'], $fallback);
}

function pluginCenterFetchList($cfg) {
    $options = pluginCenterOptions($cfg);
    if (!$options['endpoint']) return ['success' => false, 'error' => '未配置插件中心地址'];
    $url = $options['endpoint'] . (strpos($options['endpoint'], '?') === false ? '?' : '&') . http_build_query(['domain' => pluginCurrentDomain()]);
    $json = pluginHttpJson($url, $options);
    if (!$json || !is_array($json)) return ['success' => false, 'error' => '无法读取插件中心'];
    if (isset($json['success']) && !$json['success']) {
        return ['success' => false, 'error' => $json['error'] ?? '插件中心拒绝访问'];
    }
    $items = isset($json['data']) && is_array($json['data']) ? $json['data'] : $json;
    if (!is_array($items)) return ['success' => false, 'error' => '插件中心返回格式不正确'];
    $out = [];
    foreach ($items as $item) {
        if (!is_array($item)) continue;
        $valid = pluginValidateRemoteManifest($item, $options);
        if (!$valid['success']) return $valid;
        $out[] = $item;
    }
    $result = ['success' => true, 'data' => $out];
    if (isset($json['authorization']) && is_array($json['authorization'])) {
        $result['authorization'] = pluginNormalizeAuthorization($json['authorization']);
    }
    return $result;
}

function pluginCenterInstall($cfg, $id) {
    $listed = pluginCenterFetchList($cfg);
    if (!$listed['success']) return $listed;
    $target = null;
    foreach ($listed['data'] as $plugin) {
        if (($plugin['id'] ?? '') === $id || ($plugin['plugin_id'] ?? '') === $id) { $target = $plugin; break; }
    }
    if (!$target) return ['success' => false, 'error' => '插件中心没有这个插件'];
    $pid = $target['plugin_id'] ?? '';
    if ($pid !== '' && in_array($pid, pluginInstalledNames(), true)) {
        return ['success' => false, 'error' => '插件已安装，如需升级请先卸载旧版本'];
    }
    $options = pluginCenterOptions($cfg);
    $tmpDir = __DIR__ . '/../release_updates/plugin_center';
    if (!is_dir($tmpDir) && !@mkdir($tmpDir, 0755, true)) return ['success' => false, 'error' => '无法创建临时目录'];
    $fileName = pluginSafeName($target['package_name'] ?? (($pid ?: 'plugin') . '.zip'));
    $tmpFile = $tmpDir . '/' . date('Ymd_His') . '_' . $fileName;
    $download = pluginDownloadFile($target['package_url'], $tmpFile, $options);
    if (!$download['success']) { @unlink($tmpFile); return $download; }
    $sha = strtolower(hash_file('sha256', $tmpFile));
    if (!hash_equals(strtolower($target['sha256'] ?? ''), $sha)) {
        @unlink($tmpFile);
        return ['success' => false, 'error' => '插件包 SHA256 校验失败'];
    }
    $installed = pluginInstallZip($tmpFile, $pid ?: pathinfo($fileName, PATHINFO_FILENAME));
    if (!$installed['success']) return $installed;
    $installed['data']['remote'] = [
        'plugin_id' => $target['plugin_id'] ?? '',
        'version' => $target['version'] ?? '',
        'sha256' => $sha,
        'saved_as' => str_replace('\\', '/', substr($tmpFile, strlen(realpath(__DIR__ . '/..')) + 1)),
    ];
    return $installed;
}

function pluginCenterOptions($cfg) {
    $center = is_array($cfg['plugin_center'] ?? null) ? $cfg['plugin_center'] : [];
    $update = is_array($cfg['update'] ?? null) ? $cfg['update'] : [];
    $upFile = __DIR__ . '/../up.json';
    if (is_file($upFile)) {
        $up = pluginJsonDecode(file_get_contents($upFile));
        if (is_array($up)) {
            if (empty($center)) $center = is_array($up['plugin_center'] ?? null) ? $up['plugin_center'] : [];
            if (empty($update)) $update = is_array($up['update'] ?? null) ? $up['update'] : $up;
        }
    }
    if (empty($center['endpoint']) && !empty($update['endpoint'])) {
        $center['endpoint'] = preg_replace('/([?&]action=)latest\b/', '$1plugins', $update['endpoint']);
    }
    $endpoint = pluginNormalizeHttpsUrl(trim($center['endpoint'] ?? ''));
    $publicKey = trim($center['public_key'] ?? ($update['public_key'] ?? ''));
    return [
        'endpoint' => $endpoint,
        'endpoint_host' => strtolower(parse_url($endpoint, PHP_URL_HOST) ?: ''),
        'public_key' => $publicKey,
        'allow_unsigned' => !empty($center['allow_unsigned']),
        'max_package_bytes' => max(1024 * 1024, intval($center['max_package_mb'] ?? 30) * 1024 * 1024),
    ];
}

function pluginValidateRemoteManifest($manifest, $options) {
    foreach (['id', 'plugin_id', 'version', 'package_url', 'sha256'] as $field) {
        if (empty($manifest[$field]) || !is_string($manifest[$field])) {
            return ['success' => false, 'error' => '插件中心缺少字段：' . $field];
        }
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{2,80}$/', $manifest['plugin_id'])) return ['success' => false, 'error' => '插件标识格式不正确'];
    if (!preg_match('/^[a-f0-9]{64}$/i', $manifest['sha256'])) return ['success' => false, 'error' => '插件 SHA256 格式不正确'];
    if (!pluginTrustedUrl($manifest['package_url'], $options)) return ['success' => false, 'error' => '插件包地址不可信'];
    if ($options['public_key'] === '' && !$options['allow_unsigned']) return ['success' => false, 'error' => '未配置插件中心公钥'];
    if ($options['public_key'] !== '') {
        if (empty($manifest['signature'])) return ['success' => false, 'error' => '插件缺少签名'];
        if (!function_exists('sodium_crypto_sign_verify_detached')) return ['success' => false, 'error' => '服务器未开启 sodium 扩展'];
        $sig = base64_decode($manifest['signature'], true);
        $pub = base64_decode($options['public_key'], true);
        $msg = implode('|', ['plugin', $manifest['plugin_id'], $manifest['version'], strtolower($manifest['sha256'])]);
        if (!$sig || !$pub || !sodium_crypto_sign_verify_detached($sig, $msg, $pub)) return ['success' => false, 'error' => '插件签名校验失败'];
    }
    return ['success' => true];
}

function pluginInstallZip($zipFile, $fallbackName) {
    if (!class_exists('ZipArchive')) return ['success' => false, 'error' => '服务器未开启 ZipArchive 扩展'];
    $pluginsDir = realpath(__DIR__ . '/../plugins');
    if (!$pluginsDir) {
        $base = __DIR__ . '/../plugins';
        if (!@mkdir($base, 0755, true)) return ['success' => false, 'error' => '无法创建 plugins 目录'];
        $pluginsDir = realpath($base);
    }
    $zip = new ZipArchive();
    if ($zip->open($zipFile) !== true) return ['success' => false, 'error' => '无法打开插件包'];
    $scan = pluginScanZip($zip, $fallbackName);
    if (!$scan['success']) { $zip->close(); return $scan; }
    $pluginName = $scan['plugin_name'];
    $targetDir = $pluginsDir . DIRECTORY_SEPARATOR . $pluginName;
    if (is_dir($targetDir)) { $zip->close(); return ['success' => false, 'error' => '插件 ' . $pluginName . ' 已存在']; }
    if (!@mkdir($targetDir, 0755, true)) { $zip->close(); return ['success' => false, 'error' => '无法创建插件目录']; }
    foreach ($scan['files'] as $file) {
        $target = $targetDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file['rel']);
        if (!pluginPathInside($target, $targetDir)) { $zip->close(); deleteDir($targetDir); return ['success' => false, 'error' => '插件包路径不安全']; }
        $dir = dirname($target);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) { $zip->close(); deleteDir($targetDir); return ['success' => false, 'error' => '无法创建插件子目录']; }
        $in = $zip->getStream($file['entry']);
        $out = @fopen($target, 'wb');
        if (!$in || !$out) {
            if ($in) fclose($in);
            if ($out) fclose($out);
            $zip->close();
            deleteDir($targetDir);
            return ['success' => false, 'error' => '写入插件文件失败'];
        }
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);
    }
    $zip->close();
    $manifestFile = $targetDir . DIRECTORY_SEPARATOR . 'plugin.json';
    if (!is_file($manifestFile)) { deleteDir($targetDir); return ['success' => false, 'error' => '插件包缺少 plugin.json']; }
    $manifest = pluginJsonDecode(file_get_contents($manifestFile));
    if (!is_array($manifest)) { deleteDir($targetDir); return ['success' => false, 'error' => 'plugin.json 格式不正确']; }
    return ['success' => true, 'data' => ['name' => $pluginName, 'manifest' => $manifest]];
}

function pluginScanZip($zip, $fallbackName) {
    $manifestEntry = null;
    $rootDir = null;
    $total = 0;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        $entry = str_replace('\\', '/', $stat['name'] ?? '');
        if (!pluginSafeRelativePath($entry) || pluginZipEntryIsSymlink($zip, $i)) return ['success' => false, 'error' => '插件包包含不安全路径或软链接'];
        $total += intval($stat['size'] ?? 0);
        if ($total > 50 * 1024 * 1024) return ['success' => false, 'error' => '插件解压后超过 50MB'];
        if ($entry === 'plugin.json') $manifestEntry = $entry;
        elseif (preg_match('#^([A-Za-z0-9_.-]+)/plugin\.json$#', $entry, $m)) { $rootDir = $m[1]; $manifestEntry = $entry; break; }
    }
    if (!$manifestEntry) return ['success' => false, 'error' => '插件包缺少 plugin.json'];
    if ($rootDir !== null) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = str_replace('\\', '/', $zip->getNameIndex($i));
            if ($entry !== $rootDir . '/' && strpos($entry, $rootDir . '/') !== 0) return ['success' => false, 'error' => '插件包只能包含同一个插件目录'];
        }
    }
    $manifest = pluginJsonDecode($zip->getFromName($manifestEntry));
    if (!is_array($manifest)) return ['success' => false, 'error' => 'plugin.json 格式不正确'];
    $pluginName = $rootDir ?: ($manifest['id'] ?? ($manifest['plugin_id'] ?? $fallbackName));
    $pluginName = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string)$pluginName);
    $pluginName = trim($pluginName, '._');
    if ($pluginName === '' || !preg_match('/^[A-Za-z0-9_.-]{2,80}$/', $pluginName)) return ['success' => false, 'error' => '插件目录名格式不正确'];
    $files = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = str_replace('\\', '/', $zip->getNameIndex($i));
        if ($entry === '' || substr($entry, -1) === '/') continue;
        $rel = $rootDir !== null && strpos($entry, $rootDir . '/') === 0 ? substr($entry, strlen($rootDir) + 1) : $entry;
        if ($rel === '') continue;
        if (!pluginSafeRelativePath($rel)) return ['success' => false, 'error' => '插件包路径不安全'];
        $files[] = ['entry' => $entry, 'rel' => $rel];
    }
    return ['success' => true, 'plugin_name' => $pluginName, 'files' => $files];
}

function scanPlugins() {
    $dir = __DIR__ . '/../plugins/';
    $plugins = [];
    if (!is_dir($dir)) return $plugins;
    foreach (scandir($dir) as $name) {
        if ($name === '.' || $name === '..') continue;
        if (!is_dir($dir . $name)) continue;
        if ($name === 'market') continue;
        $manifestFile = $dir . $name . '/plugin.json';
        if (!file_exists($manifestFile)) continue;
        $manifest = pluginJsonDecode(file_get_contents($manifestFile));
        $plugins[] = [
            'name' => $name,
            'manifest' => $manifest,
            'installed' => true,
            'has_page' => file_exists($dir . $name . '/page.php'),
        ];
    }
    return $plugins;
}

function pluginInstalledNames() {
    $names = [];
    foreach (scanPlugins() as $plugin) {
        $names[] = $plugin['name'];
        $manifest = is_array($plugin['manifest'] ?? null) ? $plugin['manifest'] : [];
        if (!empty($manifest['id'])) $names[] = (string)$manifest['id'];
        if (!empty($manifest['plugin_id'])) $names[] = (string)$manifest['plugin_id'];
    }
    return array_values(array_unique($names));
}

function pluginHttpJson($url, $options) {
    $body = pluginHttpGet($url, 20, $options, ['Accept: application/json']);
    if ($body === false || $body === '') return null;
    return pluginJsonDecode($body);
}

function pluginHttpGet($url, $timeout, $options, $headers = []) {
    if (!function_exists('curl_init')) return false;
    $url = pluginFollowTrustedRedirects($url, $timeout, $options);
    if (!$url) return false;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => 'cx-toolbox-plugin-center',
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 300) ? $res : false;
}

function pluginDownloadFile($url, $target, $options) {
    if (!function_exists('curl_init')) return ['success' => false, 'error' => 'curl 扩展未开启'];
    $url = pluginFollowTrustedRedirects($url, 20, $options);
    if (!$url) return ['success' => false, 'error' => '插件包地址不可信'];
    $fp = @fopen($target, 'wb');
    if (!$fp) return ['success' => false, 'error' => '无法写入插件包'];
    $tooLarge = false;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => 'cx-toolbox-plugin-center',
        CURLOPT_HTTPHEADER => ['Accept: application/octet-stream'],
        CURLOPT_NOPROGRESS => false,
    ]);
    $progress = function($ch, $downloadTotal, $downloaded, $uploadTotal = 0, $uploaded = 0) use ($options, &$tooLarge) {
        if (($downloadTotal > 0 && $downloadTotal > $options['max_package_bytes']) || $downloaded > $options['max_package_bytes']) { $tooLarge = true; return 1; }
        return 0;
    };
    if (defined('CURLOPT_XFERINFOFUNCTION')) curl_setopt($ch, CURLOPT_XFERINFOFUNCTION, $progress);
    elseif (defined('CURLOPT_PROGRESSFUNCTION')) curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progress);
    $ok = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    if ($tooLarge) return ['success' => false, 'error' => '插件包超过大小限制'];
    if (!$ok || $code < 200 || $code >= 300) return ['success' => false, 'error' => '插件包下载失败：' . ($err ?: 'HTTP ' . $code)];
    return ['success' => true];
}

function pluginFollowTrustedRedirects($url, $timeout, $options) {
    for ($i = 0; $i < 5; $i++) {
        if (!pluginTrustedUrl($url, $options)) return false;
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
            CURLOPT_USERAGENT => 'cx-toolbox-plugin-center',
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
        return pluginTrustedUrl($url, $options) ? $url : false;
    }
    return false;
}

function pluginTrustedUrl($url, $options) {
    $p = parse_url($url);
    if (!$p || strtolower($p['scheme'] ?? '') !== 'https') return false;
    return strtolower($p['host'] ?? '') === $options['endpoint_host'];
}

function pluginNormalizeHttpsUrl($url) {
    $parts = parse_url($url);
    if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) return '';
    return $url;
}

function pluginCurrentDomain() {
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    $host = strtolower(trim((string)$host));
    return preg_replace('/:\d+$/', '', $host);
}

function pluginNormalizeAuthorization($auth) {
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

function pluginJsonDecode($raw) {
    if (!is_string($raw)) return null;
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    $json = json_decode($raw, true);
    return is_array($json) ? $json : null;
}

function pluginSafeRelativePath($path) {
    $path = str_replace('\\', '/', (string)$path);
    if ($path === '' || $path[0] === '/' || strpos($path, "\0") !== false || preg_match('/^[A-Za-z]:\//', $path)) return false;
    foreach (explode('/', $path) as $part) if ($part === '..') return false;
    return true;
}

function pluginPathInside($target, $root) {
    $root = realpath($root);
    if (!$root) return false;
    $dir = dirname($target);
    while (!is_dir($dir) && dirname($dir) !== $dir) $dir = dirname($dir);
    $dir = realpath($dir);
    if (!$dir) return false;
    return strpos(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) === 0;
}

function pluginZipEntryIsSymlink($zip, $index) {
    if (!method_exists($zip, 'getExternalAttributesIndex')) return false;
    $opsys = 0;
    $attr = 0;
    if (!$zip->getExternalAttributesIndex($index, $opsys, $attr)) return false;
    return ((($attr >> 16) & 0170000) === 0120000);
}

function pluginSafeName($name) {
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string)$name);
    $name = trim($name, '._');
    if ($name === '') $name = 'plugin.zip';
    if (substr(strtolower($name), -4) !== '.zip') $name .= '.zip';
    return $name;
}

function pluginFileHeader($file, $len) {
    $fp = @fopen($file, 'rb');
    if (!$fp) return '';
    $data = fread($fp, $len);
    fclose($fp);
    return $data;
}

function pluginUploadError($code) {
    $messages = [
        UPLOAD_ERR_INI_SIZE => '文件超过服务器上传限制',
        UPLOAD_ERR_FORM_SIZE => '文件超过表单上传限制',
        UPLOAD_ERR_PARTIAL => '文件上传不完整',
        UPLOAD_ERR_NO_FILE => '未选择文件',
        UPLOAD_ERR_NO_TMP_DIR => '服务器临时目录缺失',
        UPLOAD_ERR_CANT_WRITE => '服务器无法写入文件',
    ];
    return $messages[$code] ?? ('上传失败，错误码 ' . $code);
}

function deleteDir($dir) {
    if (!is_dir($dir)) return false;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path) && !is_link($path)) deleteDir($path);
        else @unlink($path);
    }
    return @rmdir($dir);
}
