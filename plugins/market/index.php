<?php
function handle_market(&$result, $source, $query, $cfg, $key) {
    if ($source === 'market_list') {
        $plugins = scanPlugins();
        $market = [
            ['name' => '密码生成器', 'id' => 'pwgen', 'desc' => '随机生成高强度密码，支持自定义长度和字符类型', 'icon' => '🔑', 'url' => 'plugins/pwgen/page.php'],
        ];
        foreach ($plugins as $p) {
            $m = $p['manifest'] ?? [];
            $pluginId = $p['name'];
            $exists = false;
            foreach ($market as &$mp) { if ($mp['id'] === $pluginId) { $exists = true; break; } }
            if (!$exists) {
                $market[] = [
                    'name' => $m['name'] ?? $pluginId,
                    'id' => $pluginId,
                    'desc' => $m['description'] ?? '',
                    'icon' => $m['icon'] ?? '🔌',
                    'url' => file_exists(__DIR__ . '/../plugins/' . $pluginId . '/page.php') ? 'plugins/' . $pluginId . '/page.php' : null,
                ];
            }
        }
        $result['success'] = true;
        $result['data'] = $market;
    }
}

function scanPlugins() {
    $dir = __DIR__ . '/../plugins/';
    $plugins = [];
    if (!is_dir($dir)) return $plugins;
    foreach (scandir($dir) as $name) {
        if ($name === '.' || $name === '..') continue;
        if (!is_dir($dir . $name)) continue;
        $mf = $dir . $name . '/plugin.json';
        if (!file_exists($mf)) continue;
        $manifest = json_decode(file_get_contents($mf), true);
        $plugins[] = ['name' => $name, 'manifest' => $manifest];
    }
    return $plugins;
}
