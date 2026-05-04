<?php
error_reporting(0);
ini_set('display_errors', 0);

function loadConfig() {
    $fp = __DIR__ . '/../config.json';
    return file_exists($fp) ? json_decode(file_get_contents($fp), true) : [];
}

function dbConnect() {
    $cfg = loadConfig();
    $db = $cfg['db'] ?? [];
    if (empty($db)) return null;
    try {
        $dbPort = !empty($db['port']) ? $db['port'] : 3306;
        return new PDO("mysql:host={$db['host']};port={$dbPort};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Exception $e) { return null; }
}

/**
 * 从请求中提取 API Key
 * 支持三种方式：?key=xxx、X-API-Key header、Authorization: Bearer xxx
 */
function extractApiKey() {
    if (!empty($_GET['key'])) return trim($_GET['key']);
    if (!empty($_SERVER['HTTP_X_API_KEY'])) return trim($_SERVER['HTTP_X_API_KEY']);
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($auth && preg_match('/Bearer\s+(.+)/i', $auth, $m)) return trim($m[1]);
    return '';
}

/**
 * 获取客户端真实 IP
 */
function clientIP() {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip && strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
    return $ip;
}

/**
 * 验证 API Key 并返回 key 信息
 * 同时兼容旧的 users.api_key 字段
 */
function verifyApiKey($key) {
    if (!$key) return null;
    $pdo = dbConnect();
    if (!$pdo) return null;

    // 新版 api_keys 表
    try {
        $stmt = $pdo->prepare("SELECT k.*, u.username, u.role FROM api_keys k LEFT JOIN users u ON k.user_id = u.id WHERE k.api_key = ? AND k.status = 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if ($row) {
            // 检查过期
            if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) return null;
            return $row;
        }
    } catch (Exception $e) {}

    // 兼容旧版 users.api_key
    try {
        $stmt = $pdo->prepare("SELECT id as user_id, username, role FROM users WHERE api_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if ($row) {
            return array_merge($row, [
                'id' => 0,
                'api_key' => $key,
                'name' => '用户默认密钥',
                'status' => 1,
                'permissions' => '*',
                'rate_limit' => 0,
                'legacy' => true,
            ]);
        }
    } catch (Exception $e) {}
    return null;
}

/**
 * 检查调用频率限制
 */
function checkRateLimit($keyId, $limit) {
    if (!$keyId || !$limit || $limit <= 0) return true;
    $pdo = dbConnect();
    if (!$pdo) return true;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_logs WHERE api_key_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
        $stmt->execute([$keyId]);
        $count = (int)$stmt->fetchColumn();
        return $count < $limit;
    } catch (Exception $e) { return true; }
}

/**
 * 记录 API 调用日志
 */
function logApiCall($keyId, $userId, $source, $status, $error = null) {
    $pdo = dbConnect();
    if (!$pdo) return;
    try {
        $stmt = $pdo->prepare("INSERT INTO api_logs (api_key_id, user_id, source, ip, user_agent, status, error, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $keyId ?: null,
            $userId ?: null,
            $source,
            clientIP(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            $status,
            $error ? substr($error, 0, 500) : null,
        ]);
        // 更新 key 的使用次数和最后使用时间
        if ($keyId) {
            $pdo->prepare("UPDATE api_keys SET used_count = used_count + 1, last_used_at = NOW(), last_used_ip = ? WHERE id = ?")
                ->execute([clientIP(), $keyId]);
        }
    } catch (Exception $e) {}
}

/**
 * 检查 Key 是否有调用某 source 的权限
 * permissions 字段格式：'*' 全部 | 'ip,whois,weather' 逗号分隔
 */
function checkPermission($permissions, $source) {
    if (!$permissions || $permissions === '*') return true;
    $allowed = array_map('trim', explode(',', $permissions));
    // 支持通配符 ip-* 匹配 ip-api / ip-sb 等
    foreach ($allowed as $p) {
        if ($p === $source) return true;
        if (substr($p, -1) === '*' && strpos($source, rtrim($p, '*')) === 0) return true;
    }
    return false;
}

/**
 * 认证用户（综合 session 和 api key）
 */
function authUser() {
    @session_start();
    if (!empty($_SESSION['user_id'])) return $_SESSION;
    $key = extractApiKey();
    if ($key) {
        $info = verifyApiKey($key);
        if ($info) {
            return [
                'user_id' => $info['user_id'],
                'username' => $info['username'] ?? '',
                'role' => $info['role'] ?? 'user',
                'api_auth' => true,
                'api_key_id' => $info['id'] ?? 0,
            ];
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

// 完全公开的 API（无需任何认证，无需 key）
$publicSources = ['file_config','file_list','myip','ip-api','ip-sb','ipwhois','ipip','ip-baidu','ip-baota','weather_amap','whois','icp','ip9','user_register','user_login','msg_add','msg_list','cat_list','link_list','market_list','plugin_install','plugin_list','plugin_delete','api_docs'];

// 管理员专属 API（必须管理员 session 或 admin 级别 key）
$adminOnlySources = ['apikey_list_all','apikey_logs_all','apikey_admin_create','apikey_admin_delete','apikey_admin_toggle'];

$source = $_GET['source'] ?? 'ip-api';
$query = $_GET['query'] ?? '';
$cfg = loadConfig();
$key = $cfg['amap_key'] ?? '';
$result = ['source' => $source, 'success' => false, 'data' => [], 'error' => null];

// 未安装拦截
if (!in_array($source, $publicSources, true) && empty($cfg['installed'])) {
    jsonExit(array_merge($result, ['error' => '请先完成安装']));
}

// ====== 统一 API Key 鉴权 ======
$apiKeyInfo = null;
$requireKey = false; // 默认：session 已登录则放行，否则需要 key

@session_start();
$sessionAuthed = !empty($_SESSION['user_id']);
$providedKey = extractApiKey();

// 如果提供了 key，优先验证 key（即使 session 已登录）
if ($providedKey) {
    $apiKeyInfo = verifyApiKey($providedKey);
    if (!$apiKeyInfo) {
        logApiCall(0, 0, $source, 'fail', '无效的 API Key');
        jsonExit(array_merge($result, ['error' => '无效的 API Key 或已被禁用', 'code' => 401]));
    }
    // 权限校验
    if (!checkPermission($apiKeyInfo['permissions'] ?? '*', $source)) {
        logApiCall($apiKeyInfo['id'] ?? 0, $apiKeyInfo['user_id'] ?? 0, $source, 'fail', '无权访问该接口');
        jsonExit(array_merge($result, ['error' => '该 Key 无权访问接口：' . $source, 'code' => 403]));
    }
    // 限流
    $rateLimit = (int)($apiKeyInfo['rate_limit'] ?? 0);
    if (!checkRateLimit($apiKeyInfo['id'] ?? 0, $rateLimit)) {
        logApiCall($apiKeyInfo['id'] ?? 0, $apiKeyInfo['user_id'] ?? 0, $source, 'fail', '请求过于频繁');
        jsonExit(array_merge($result, ['error' => '请求过于频繁，请稍后再试（限制：每分钟 ' . $rateLimit . ' 次）', 'code' => 429]));
    }
    // 填充 session 用于下游
    $_SESSION['admin'] = true;
    $_SESSION['user_id'] = $apiKeyInfo['user_id'];
    $_SESSION['username'] = $apiKeyInfo['username'] ?? '';
    $_SESSION['role'] = $apiKeyInfo['role'] ?? 'user';
    $_SESSION['api_auth'] = true;
} else if (!in_array($source, $publicSources, true) && !$sessionAuthed) {
    // 非公开接口且未登录、未提供 key
    logApiCall(0, 0, $source, 'fail', '未授权');
    jsonExit(array_merge($result, ['error' => '未授权访问，请登录或提供有效的 API Key', 'code' => 401]));
}

// 管理员专属 API 二次校验
if (in_array($source, $adminOnlySources, true)) {
    $role = $apiKeyInfo['role'] ?? $_SESSION['role'] ?? '';
    if ($role !== 'admin') {
        logApiCall($apiKeyInfo['id'] ?? 0, $_SESSION['user_id'] ?? 0, $source, 'fail', '需要管理员权限');
        jsonExit(array_merge($result, ['error' => '该接口仅管理员可用', 'code' => 403]));
    }
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
    'link_list' => 'link', 'link_add' => 'link', 'link_delete' => 'link',
    'market_list' => 'market',
    'apikey_list' => 'apikey', 'apikey_create' => 'apikey', 'apikey_delete' => 'apikey', 'apikey_toggle' => 'apikey',
    'apikey_update' => 'apikey', 'apikey_logs' => 'apikey', 'apikey_stats' => 'apikey',
    'apikey_list_all' => 'apikey', 'apikey_logs_all' => 'apikey', 'apikey_admin_create' => 'apikey',
    'apikey_admin_delete' => 'apikey', 'apikey_admin_toggle' => 'apikey',
    'api_docs' => 'docs',
];

try {
    $module = $moduleMap[$source] ?? null;
    if ($module) {
        include __DIR__ . "/{$module}.php";
        $fn = "handle_{$module}";
        if (function_exists($fn)) $fn($result, $source, $query, $cfg, $key);
    }
    if (!$module && $source) {
        $pluginsDir = __DIR__ . '/../plugins';
        if (is_dir($pluginsDir)) {
            foreach (scandir($pluginsDir) as $pname) {
                if ($pname === '.' || $pname === '..') continue;
                $pdir = "{$pluginsDir}/{$pname}";
                if (!is_dir($pdir)) continue;
                $mf = "{$pdir}/plugin.json";
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
} catch (Exception $e) {
    $result = ['source' => $source, 'success' => false, 'data' => [], 'error' => '服务器内部错误'];
}

// 记录日志（仅在使用 api key 调用时记录，session 登录不记录避免噪音）
if ($apiKeyInfo) {
    logApiCall(
        $apiKeyInfo['id'] ?? 0,
        $apiKeyInfo['user_id'] ?? 0,
        $source,
        $result['success'] ? 'ok' : 'fail',
        $result['success'] ? null : ($result['error'] ?? null)
    );
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
