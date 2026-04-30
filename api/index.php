<?php
function loadConfig() {
    $fp = __DIR__ . '/../config.json';
    return file_exists($fp) ? json_decode(file_get_contents($fp), true) : [];
}

function dbConnect() {
    $cfg = loadConfig();
    $db = $cfg['db'] ?? [];
    if (empty($db)) return null;
    try {
        return new PDO("mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Exception $e) { return null; }
}

function authUser() {
    @session_start();
    if (!empty($_SESSION['user_id'])) return $_SESSION;
    $key = $_GET['key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($key) {
        $pdo = dbConnect();
        if ($pdo) {
            try { $pdo->exec("ALTER TABLE users ADD COLUMN api_key VARCHAR(64) DEFAULT NULL"); } catch (Exception $e) {}
            $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE api_key = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row) return ['user_id' => $row['id'], 'username' => $row['username'], 'role' => $row['role'], 'api_auth' => true];
        }
    }
    return null;
}

function httpGet($url, $timeout = 8) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false, CURLOPT_USERAGENT => 'Mozilla/5.0',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 200 && $code < 300) return $res;
        return false;
    }
    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'user_agent' => 'Mozilla/5.0']]);
    return @file_get_contents($url, false, $ctx);
}

function httpGetRaw($url, $timeout = 8) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => false, CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code >= 200 && $code < 300 ? $res : false;
}

function jsonExit($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$publicSources = ['file_config','file_list','myip','ip-api','ip-sb','ipwhois','ipip','ip-baidu','ip-baota','weather_amap','whois','icp','ip9','user_register','user_login','msg_add','msg_list','cat_list','market_list'];
$source = $_GET['source'] ?? 'ip-api';
$query = $_GET['query'] ?? '';
$cfg = loadConfig();
$key = $cfg['amap_key'] ?? '';
$result = ['source' => $source, 'success' => false, 'data' => [], 'error' => null];

if (!in_array($source, $publicSources, true) && empty($cfg['installed'])) {
    jsonExit(array_merge($result, ['error' => '请先完成安装']));
}

$moduleMap = [
    'ip-api' => 'ip', 'ip-sb' => 'ip', 'ipwhois' => 'ip', 'ipip' => 'ip', 'ip-baidu' => 'ip', 'ip-baota' => 'ip', 'ip9' => 'ip',
    'weather_amap' => 'weather',
    'whois' => 'whois',
    'icp' => 'icp',
    'myip' => 'ip',
    'file_list' => 'file', 'file_upload' => 'file', 'file_upload_array' => 'file', 'file_delete' => 'file', 'file_download' => 'file', 'file_config' => 'file',
    'user_register' => 'user', 'user_login' => 'user', 'user_logout' => 'user', 'user_info' => 'user', 'user_api_key' => 'user', 'user_list' => 'user', 'user_delete' => 'user',
    'admin_login' => 'admin', 'admin_check' => 'admin', 'admin_logout' => 'admin', 'admin_save' => 'admin', 'admin_files' => 'admin',
    'msg_add' => 'message', 'msg_list' => 'message', 'msg_delete' => 'message',
    'plugin_list' => 'plugin', 'plugin_delete' => 'plugin', 'plugin_install' => 'plugin',
    'cat_list' => 'category', 'cat_add' => 'category', 'cat_delete' => 'category', 'cat_mode' => 'category', 'cat_batch_mode' => 'category',
    'market_list' => 'market',
];

$module = $moduleMap[$source] ?? null;
if ($module) {
    include __DIR__ . "/{$module}.php";
    $fn = "handle_{$module}";
    if (function_exists($fn)) $fn($result, $source, $query, $cfg, $key);
}
// 插件动态路由：扫描 plugins/ 目录查找注册的 API
if (!$module && $source) {
    $pluginsDir = __DIR__ . '/../plugins';
    if (is_dir($pluginsDir)) {
        foreach (scandir($pluginsDir) as $pname) {
            if ($pname === '.' || $pname === '..') continue;
            $mf = "{$pluginsDir}/{$pname}/plugin.json";
            if (!file_exists($mf)) continue;
            $manifest = json_decode(file_get_contents($mf), true);
            $apis = $manifest['api'] ?? [];
            if (in_array($source, $apis)) {
                $entry = $manifest['entry'] ?? 'index.php';
                $entryFile = "{$pluginsDir}/{$pname}/{$entry}";
                if (file_exists($entryFile)) {
                    include $entryFile;
                    $fn = "handle_{$pname}";
                    if (function_exists($fn)) $fn($result, $source, $query, $cfg, $key);
                }
                break;
            }
        }
    }
}
echo json_encode($result, JSON_UNESCAPED_UNICODE);
