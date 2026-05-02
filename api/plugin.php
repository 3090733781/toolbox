<?php
function handle_plugin(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'plugin_list':
            $result['success'] = true;
            $result['data'] = scanPlugins();
            return;

        case 'plugin_delete':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $name = basename($_GET['name'] ?? '');
            if (!$name) { $result['error'] = '缺少插件名'; return; }
            $pluginDir = __DIR__ . '/../plugins/' . $name;
            if (!is_dir($pluginDir)) { $result['error'] = '插件不存在'; return; }
            if (!deleteDir($pluginDir)) { $result['error'] = '删除失败'; return; }
            $result['success'] = true;
            $result['data'] = ['deleted' => $name];
            return;

        case 'plugin_install':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) { $result['error'] = '请上传插件包'; return; }
            $f = $_FILES['file'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errMsgs = [
                    UPLOAD_ERR_INI_SIZE => '文件超过服务器上传限制',
                    UPLOAD_ERR_FORM_SIZE => '文件超过表单限制',
                    UPLOAD_ERR_PARTIAL => '文件上传不完整',
                    UPLOAD_ERR_NO_FILE => '未选择文件',
                    UPLOAD_ERR_NO_TMP_DIR => '服务器临时目录缺失',
                    UPLOAD_ERR_CANT_WRITE => '无法写入磁盘',
                ];
                $result['error'] = $errMsgs[$f['error']] ?? '上传失败 (错误码: ' . $f['error'] . ')';
                return;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if ($ext !== 'zip') { $result['error'] = '仅支持 ZIP 格式'; return; }
            $pluginName = pathinfo($f['name'], PATHINFO_FILENAME);
            $pluginsDir = __DIR__ . '/../plugins/';
            $tmpFile = $f['tmp_name'];
            $extracted = false;
            $extractDir = '';

            // 方法1: ZipArchive（推荐）
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($tmpFile) === TRUE) {
                    // 扫描 ZIP 内所有文件，找到 plugin.json 的位置
                    $foundDir = null;
                    $rootPluginJson = false;
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entry = $zip->getNameIndex($i);
                        if (preg_match('#^([^/]+)/plugin\.json$#', $entry, $m)) {
                            $foundDir = $m[1];
                            break;
                        }
                        if ($entry === 'plugin.json') {
                            $rootPluginJson = true;
                        }
                    }
                    if ($foundDir !== null) {
                        $exDir = $pluginsDir . $foundDir;
                        if (!is_dir($exDir)) {
                            @$zip->extractTo($pluginsDir);
                            if (file_exists($exDir . '/plugin.json')) {
                                $extracted = true;
                                $pluginName = $foundDir;
                                $extractDir = $exDir;
                            }
                        } else {
                            $result['error'] = '插件「' . $foundDir . '」已存在';
                            $zip->close();
                            return;
                        }
                    } elseif ($rootPluginJson) {
                        $exDir = $pluginsDir . $pluginName;
                        if (!is_dir($exDir)) {
                            @mkdir($exDir, 0755, true);
                            @$zip->extractTo($exDir);
                            if (file_exists($exDir . '/plugin.json')) {
                                $extracted = true;
                                $extractDir = $exDir;
                                $manifest = json_decode(file_get_contents($exDir . '/plugin.json'), true);
                                if (!empty($manifest['name'])) $pluginName = $manifest['name'];
                            } else {
                                deleteDir($exDir);
                            }
                        } else {
                            $result['error'] = '插件「' . $pluginName . '」已存在';
                            $zip->close();
                            return;
                        }
                    }
                    $zip->close();
                }
            }

            // 方法2: 系统 unzip 命令
            if (!$extracted && function_exists('exec')) {
                // 先检查 ZIP 结构，确定是否含子目录
                @exec("unzip -l " . escapeshellarg($tmpFile) . " 2>&1", $listOut, $listCode);
                $hasSubdir = false;
                foreach ($listOut as $line) {
                    if (preg_match('#^\s*[\d]+\s+[\d-]+\s+[\d:]+\s+([^/]+)/plugin\.json\s*$#', $line)) {
                        $hasSubdir = true;
                        break;
                    }
                }
                if ($hasSubdir) {
                    @exec("unzip -o " . escapeshellarg($tmpFile) . " -d " . escapeshellarg($pluginsDir) . " 2>&1", $out, $code);
                    foreach (scandir($pluginsDir) as $item) {
                        if ($item === '.' || $item === '..' || !is_dir($pluginsDir . $item)) continue;
                        if (file_exists($pluginsDir . $item . '/plugin.json')) {
                            $pluginName = $item;
                            $extractDir = $pluginsDir . $item;
                            $extracted = true;
                            break;
                        }
                    }
                } else {
                    // 无子目录，先创建插件目录再解压
                    $exDir = $pluginsDir . $pluginName;
                    if (!is_dir($exDir)) @mkdir($exDir, 0755, true);
                    @exec("unzip -o " . escapeshellarg($tmpFile) . " -d " . escapeshellarg($exDir) . " 2>&1", $out, $code);
                    if (file_exists($exDir . '/plugin.json')) {
                        $extracted = true;
                        $extractDir = $exDir;
                        $manifest = json_decode(file_get_contents($exDir . '/plugin.json'), true);
                        if (!empty($manifest['name'])) $pluginName = $manifest['name'];
                    } else {
                        deleteDir($exDir);
                    }
                }
            }

            // 方法3: zip_open (PHP 7.x)
            if (!$extracted && PHP_VERSION_ID < 80000 && function_exists('zip_open')) {
                $z = @zip_open($tmpFile);
                if (is_resource($z)) {
                    // 先扫描找到 plugin.json 所在的根目录
                    $foundDir = null;
                    $rootPluginJson = false;
                    while ($entry = @zip_read($z)) {
                        $name = @zip_entry_name($entry);
                        if (preg_match('#^([^/]+)/plugin\.json$#', $name, $m)) { $foundDir = $m[1]; break; }
                        if ($name === 'plugin.json') { $rootPluginJson = true; }
                    }
                    @zip_close($z);

                    if ($foundDir) {
                        $exDir = $pluginsDir . $foundDir;
                        if (!is_dir($exDir)) {
                            $z = @zip_open($tmpFile);
                            if (is_resource($z)) {
                                if (!is_dir($exDir)) @mkdir($exDir, 0755, true);
                                while ($entry = @zip_read($z)) {
                                    $name = @zip_entry_name($entry);
                                    // 只提取 foundDir 下的文件
                                    if (strpos($name, $foundDir . '/') !== 0 && $name !== $foundDir . '/') continue;
                                    $relPath = substr($name, strlen($foundDir) + 1);
                                    if ($relPath === '' || $relPath === false) continue;
                                    $fullPath = $exDir . '/' . $relPath;
                                    if (substr($name, -1) === '/') { @mkdir($fullPath, 0755, true); continue; }
                                    $dir = dirname($fullPath);
                                    if (!is_dir($dir)) @mkdir($dir, 0755, true);
                                    if (@zip_entry_open($z, $entry)) {
                                        $content = @zip_entry_read($entry, @zip_entry_filesize($entry));
                                        if ($content !== false) @file_put_contents($fullPath, $content);
                                        @zip_entry_close($entry);
                                    }
                                }
                                @zip_close($z);
                                if (file_exists($exDir . '/plugin.json')) {
                                    $extracted = true;
                                    $pluginName = $foundDir;
                                    $extractDir = $exDir;
                                } else { deleteDir($exDir); }
                            }
                        } else {
                            $result['error'] = '插件「' . $foundDir . '」已存在';
                            return;
                        }
                    } elseif ($rootPluginJson) {
                        $exDir = $pluginsDir . $pluginName;
                        if (!is_dir($exDir)) {
                            $z = @zip_open($tmpFile);
                            if (is_resource($z)) {
                                if (!is_dir($exDir)) @mkdir($exDir, 0755, true);
                                while ($entry = @zip_read($z)) {
                                    $name = @zip_entry_name($entry);
                                    if (substr($name, -1) === '/') { @mkdir($exDir . '/' . $name, 0755, true); continue; }
                                    $fullPath = $exDir . '/' . $name;
                                    $dir = dirname($fullPath);
                                    if (!is_dir($dir)) @mkdir($dir, 0755, true);
                                    if (@zip_entry_open($z, $entry)) {
                                        $content = @zip_entry_read($entry, @zip_entry_filesize($entry));
                                        if ($content !== false) @file_put_contents($fullPath, $content);
                                        @zip_entry_close($entry);
                                    }
                                }
                                @zip_close($z);
                                if (file_exists($exDir . '/plugin.json')) {
                                    $extracted = true;
                                    $extractDir = $exDir;
                                    $manifest = json_decode(file_get_contents($exDir . '/plugin.json'), true);
                                    if (!empty($manifest['name'])) $pluginName = $manifest['name'];
                                } else { deleteDir($exDir); }
                            }
                        } else {
                            $result['error'] = '插件「' . $pluginName . '」已存在';
                            return;
                        }
                    }
                } elseif (is_int($z)) {
                    // zip_open 返回错误码
                }
            }

            // 方法4: 手动解析 ZIP（纯 PHP 回退，无需扩展）
            if (!$extracted) {
                $fp = fopen($tmpFile, 'rb');
                if ($fp) {
                    // 先用 End of Central Directory 找到文件列表
                    fseek($fp, -22, SEEK_END);
                    $eocd = fread($fp, 22);
                    if (strlen($eocd) === 22) {
                        $eocdSig = unpack('V', substr($eocd, 0, 4))[1];
                        if ($eocdSig === 0x06054b50) {
                            $totalEntries = unpack('v', substr($eocd, 8, 2))[1];
                            $cdSize = unpack('V', substr($eocd, 12, 4))[1];
                            $cdOffset = unpack('V', substr($eocd, 16, 4))[1];

                            // 先扫描找到 plugin.json 的根目录
                            $foundDir = null;
                            $rootPluginJson = false;
                            fseek($fp, $cdOffset);
                            for ($i = 0; $i < $totalEntries; $i++) {
                                $sig = unpack('V', fread($fp, 4))[1];
                                if ($sig !== 0x02014b50) break;
                                fseek($fp, 24, SEEK_CUR);
                                $nameLen = unpack('v', fread($fp, 2))[1];
                                $extraLen = unpack('v', fread($fp, 2))[1];
                                $commentLen = unpack('v', fread($fp, 2))[1];
                                fseek($fp, 8, SEEK_CUR);
                                $filename = fread($fp, $nameLen);
                                fseek($fp, $extraLen + $commentLen, SEEK_CUR);
                                if (preg_match('#^([^/]+)/plugin\.json$#', $filename, $m)) {
                                    $foundDir = $m[1];
                                    break;
                                }
                                if ($filename === 'plugin.json') {
                                    $rootPluginJson = true;
                                }
                            }

                            if ($foundDir) {
                                $exDir = $pluginsDir . $foundDir;
                                if (is_dir($exDir)) {
                                    fclose($fp);
                                    $result['error'] = '插件「' . $foundDir . '」已存在';
                                    return;
                                }
                                if (!is_dir($exDir)) @mkdir($exDir, 0755, true);

                                // 重新扫描并提取文件
                                fseek($fp, $cdOffset);
                                for ($i = 0; $i < $totalEntries; $i++) {
                                    $sig = unpack('V', fread($fp, 4))[1];
                                    if ($sig !== 0x02014b50) break;
                                    fseek($fp, 16, SEEK_CUR);
                                    $compressedSize = unpack('V', fread($fp, 4))[1];
                                    $uncompressedSize = unpack('V', fread($fp, 4))[1];
                                    $nameLen = unpack('v', fread($fp, 2))[1];
                                    $extraLen = unpack('v', fread($fp, 2))[1];
                                    $commentLen = unpack('v', fread($fp, 2))[1];
                                    fseek($fp, 8, SEEK_CUR);
                                    $localOffset = unpack('V', fread($fp, 4))[1];
                                    $filename = fread($fp, $nameLen);
                                    fseek($fp, $extraLen + $commentLen, SEEK_CUR);

                                    if (strpos($filename, $foundDir . '/') !== 0 && $filename !== $foundDir . '/') continue;
                                    $relPath = substr($filename, strlen($foundDir) + 1);
                                    if ($relPath === '' || $relPath === false) continue;
                                    if (substr($filename, -1) === '/') {
                                        @mkdir($exDir . '/' . $relPath, 0755, true);
                                        continue;
                                    }

                                    $curPos = ftell($fp);
                                    fseek($fp, $localOffset);
                                    $localSig = unpack('V', fread($fp, 4))[1];
                                    if ($localSig !== 0x04034b50) { fseek($fp, $curPos); continue; }
                                    fseek($fp, 22, SEEK_CUR);
                                    $lnameLen = unpack('v', fread($fp, 2))[1];
                                    $lextraLen = unpack('v', fread($fp, 2))[1];
                                    fseek($fp, $lnameLen + $lextraLen, SEEK_CUR);

                                    $data = fread($fp, $compressedSize);
                                    if ($compressedSize < $uncompressedSize && function_exists('gzinflate')) {
                                        $dec = @gzinflate($data);
                                        if ($dec !== false) $data = $dec;
                                    }
                                    $dir = dirname($exDir . '/' . $relPath);
                                    if (!is_dir($dir)) @mkdir($dir, 0755, true);
                                    @file_put_contents($exDir . '/' . $relPath, $data);
                                    fseek($fp, $curPos);
                                }
                                if (file_exists($exDir . '/plugin.json')) {
                                    $extracted = true;
                                    $pluginName = $foundDir;
                                    $extractDir = $exDir;
                                } else {
                                    deleteDir($exDir);
                                }
                            } elseif ($rootPluginJson) {
                                $exDir = $pluginsDir . $pluginName;
                                if (is_dir($exDir)) {
                                    fclose($fp);
                                    $result['error'] = '插件「' . $pluginName . '」已存在';
                                    return;
                                }
                                if (!is_dir($exDir)) @mkdir($exDir, 0755, true);

                                fseek($fp, $cdOffset);
                                for ($i = 0; $i < $totalEntries; $i++) {
                                    $sig = unpack('V', fread($fp, 4))[1];
                                    if ($sig !== 0x02014b50) break;
                                    fseek($fp, 16, SEEK_CUR);
                                    $compressedSize = unpack('V', fread($fp, 4))[1];
                                    $uncompressedSize = unpack('V', fread($fp, 4))[1];
                                    $nameLen = unpack('v', fread($fp, 2))[1];
                                    $extraLen = unpack('v', fread($fp, 2))[1];
                                    $commentLen = unpack('v', fread($fp, 2))[1];
                                    fseek($fp, 8, SEEK_CUR);
                                    $localOffset = unpack('V', fread($fp, 4))[1];
                                    $filename = fread($fp, $nameLen);
                                    fseek($fp, $extraLen + $commentLen, SEEK_CUR);

                                    if (substr($filename, -1) === '/') {
                                        @mkdir($exDir . '/' . $filename, 0755, true);
                                        continue;
                                    }

                                    $curPos = ftell($fp);
                                    fseek($fp, $localOffset);
                                    $localSig = unpack('V', fread($fp, 4))[1];
                                    if ($localSig !== 0x04034b50) { fseek($fp, $curPos); continue; }
                                    fseek($fp, 22, SEEK_CUR);
                                    $lnameLen = unpack('v', fread($fp, 2))[1];
                                    $lextraLen = unpack('v', fread($fp, 2))[1];
                                    fseek($fp, $lnameLen + $lextraLen, SEEK_CUR);

                                    $data = fread($fp, $compressedSize);
                                    if ($compressedSize < $uncompressedSize && function_exists('gzinflate')) {
                                        $dec = @gzinflate($data);
                                        if ($dec !== false) $data = $dec;
                                    }
                                    $dir = dirname($exDir . '/' . $filename);
                                    if (!is_dir($dir)) @mkdir($dir, 0755, true);
                                    @file_put_contents($exDir . '/' . $filename, $data);
                                    fseek($fp, $curPos);
                                }
                                if (file_exists($exDir . '/plugin.json')) {
                                    $extracted = true;
                                    $extractDir = $exDir;
                                    $manifest = json_decode(file_get_contents($exDir . '/plugin.json'), true);
                                    if (!empty($manifest['name'])) $pluginName = $manifest['name'];
                                } else {
                                    deleteDir($exDir);
                                }
                            }
                        }
                    }
                    fclose($fp);
                }
            }

            if (!$extracted) {
                $result['error'] = '解压失败：请确认 ZIP 包根目录包含 plugin.json，或通过 FTP 将插件上传到 plugins/ 目录';
                return;
            }
            if (!file_exists($extractDir . '/plugin.json')) { deleteDir($extractDir); $result['error'] = '缺少 plugin.json'; return; }
            $manifest = json_decode(file_get_contents($extractDir . '/plugin.json'), true);
            $result['success'] = true;
            $result['data'] = ['name' => $pluginName, 'manifest' => $manifest];
            return;
    }
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
        $manifest = json_decode(file_get_contents($manifestFile), true);
        $plugins[] = [
            'name' => $name,
            'manifest' => $manifest,
            'installed' => true,
            'has_page' => file_exists($dir . $name . '/page.php'),
        ];
    }
    return $plugins;
}

function deleteDir($dir) {
    if (!is_dir($dir)) return false;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) deleteDir($path);
        else @unlink($path);
    }
    return @rmdir($dir);
}
