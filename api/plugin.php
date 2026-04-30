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
            if ($f['error'] !== UPLOAD_ERR_OK) { $result['error'] = '上传失败'; return; }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if ($ext !== 'zip') { $result['error'] = '仅支持 ZIP 格式'; return; }
            $pluginName = pathinfo($f['name'], PATHINFO_FILENAME);
            $extractDir = __DIR__ . '/../plugins/' . $pluginName;
            if (is_dir($extractDir)) { $result['error'] = '插件「' . $pluginName . '」已存在'; return; }
            $tmpFile = $f['tmp_name'];
            $extracted = false;
            // 尝试 ZipArchive
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($tmpFile) === TRUE) {
                    $first = $zip->getNameIndex(0);
                    $pName = (strpos($first, '/') !== false) ? explode('/', $first)[0] : pathinfo($first, PATHINFO_FILENAME);
                    $exDir = __DIR__ . '/../plugins/' . $pName;
                    if (!is_dir($exDir)) { @$zip->extractTo(__DIR__ . '/../plugins/'); if (file_exists($exDir . '/plugin.json')) { $extracted = true; $pluginName = $pName; $extractDir = $exDir; } }
                    $zip->close();
                }
            }
            // 尝试系统 unzip
            if (!$extracted && function_exists('exec')) {
                $pluginsDir = __DIR__ . '/../plugins/';
                @exec("unzip -o " . escapeshellarg($tmpFile) . " -d " . escapeshellarg($pluginsDir) . " 2>/dev/null", $out, $code);
                if ($code === 0) {
                    foreach (scandir($pluginsDir) as $item) {
                        if ($item === '.' || $item === '..' || !is_dir($pluginsDir . $item)) continue;
                        if (file_exists($pluginsDir . $item . '/plugin.json')) { $pluginName = $item; $extractDir = $pluginsDir . $item; $extracted = true; break; }
                    }
                }
            }
            if (!$extracted && function_exists('zip_open')) {
                $z = zip_open($tmpFile);
                if (is_resource($z)) {
                    $pName = $pluginName;
                    $exDir = __DIR__ . '/../plugins/' . $pName;
                    if (!is_dir($exDir)) @mkdir($exDir, 0755, true);
                    while ($entry = zip_read($z)) {
                        $name = zip_entry_name($entry);
                        $parts = explode('/', $name);
                        $relPath = implode('/', array_slice($parts, 1));
                        if (!$relPath) continue;
                        $fullPath = $exDir . '/' . $relPath;
                        if (substr($name, -1) === '/') { @mkdir($fullPath, 0755, true); continue; }
                        @mkdir(dirname($fullPath), 0755, true);
                        if (zip_entry_open($z, $entry)) {
                            $content = zip_entry_read($entry, zip_entry_filesize($entry));
                            if ($content !== false) file_put_contents($fullPath, $content);
                            zip_entry_close($entry);
                        }
                    }
                    zip_close($z);
                    if (file_exists($exDir . '/plugin.json')) { $extracted = true; $pluginName = $pName; $extractDir = $exDir; }
                    else { deleteDir($exDir); }
                }
            }
            if (!$extracted) {
                $result['error'] = '服务器不支持ZIP解压，请通过FTP将插件上传到 plugins/ 目录';
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
        $manifestFile = $dir . $name . '/plugin.json';
        if (!file_exists($manifestFile)) continue;
        $manifest = json_decode(file_get_contents($manifestFile), true);
        $plugins[] = [
            'name' => $name,
            'manifest' => $manifest,
            'installed' => true,
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
