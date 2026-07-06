<?php
function handle_admin(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'admin_login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $pwd = $input['password'] ?? ''; $hash = $cfg['password_hash'] ?? '';
            if (!$hash) { $result['success'] = true; $result['data'] = ['need_setup' => true]; return; }
            if (password_verify($pwd, $hash)) { toolbox_session_start(); toolbox_session_regenerate(); $_SESSION['admin'] = true; $_SESSION['role'] = 'admin'; $result['success'] = true; }
            else { $result['error'] = '密码错误'; }
            return;

        case 'admin_check':
            toolbox_session_start(); $result['success'] = !empty($_SESSION['admin']); return;

        case 'admin_logout':
            toolbox_session_start(); $_SESSION = []; 
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            toolbox_session_destroy(); 
            $result['success'] = true; 
            return;

        case 'admin_save':
            toolbox_session_start(); $cfg = loadConfig();
            $rawBody = file_get_contents('php://input'); $input = json_decode($rawBody, true);
            if (!$input) { $result['error'] = 'invalid data'; return; }
            
            $isFirstSetup = empty($cfg['password_hash']) && !empty($input['password'] ?? '');
            if ($isFirstSetup) {
                // 首次安装时只能在本地或者安装向导页面设置密码
                $localIPs = ['127.0.0.1', '::1', 'localhost'];
                $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
                $referer = $_SERVER['HTTP_REFERER'] ?? '';
                $isInstallPage = strpos($referer, '/install/') !== false;
                if (!$isInstallPage && !in_array($clientIP, $localIPs)) {
                    $result['error'] = 'first admin password setup must be performed locally or via installer';
                    return;
                }
            } elseif (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $result['error'] = 'permission denied';
                return;
            }
            
            if (!empty($input['amap_key']) && (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')) { $result['error'] = 'admin only'; return; }
            if (!empty($input['password'])) {
                // 优化哈希算法，和用户模块保持一致
                if (strlen($input['password']) < 6) {
                    $result['error'] = 'admin password must be at least 6 characters';
                    return;
                }
                if (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $input['password'])) { 
                    $result['error'] = 'password must contain both letters and numbers'; 
                    return; 
                }
                
                // 使用更安全的哈希算法
                if (defined('PASSWORD_ARGON2ID')) {
                    $cfg['password_hash'] = password_hash($input['password'], PASSWORD_ARGON2ID, [
                        'memory_cost' => 1<<17,
                        'time_cost' => 4,
                        'threads' => 3,
                    ]);
                } else {
                    $cfg['password_hash'] = password_hash($input['password'], PASSWORD_BCRYPT, [
                        'cost' => 12,
                    ]);
                }
                
                $pdo = dbConnect();
                if ($pdo && !empty($_SESSION['user_id'])) {
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$cfg['password_hash'], $_SESSION['user_id']]);
                }
            }
            if (isset($input['amap_key'])) $cfg['amap_key'] = $input['amap_key'];
            if (isset($input['icp_api'])) $cfg['icp_api'] = $input['icp_api'];
            if (isset($input['max_file_size_mb'])) $cfg['max_file_size_mb'] = intval($input['max_file_size_mb']);
            if (isset($input['ip_sources'])) {
                if (!isset($cfg['ip_sources'])) $cfg['ip_sources'] = [];
                foreach ($input['ip_sources'] as $k => $v) $cfg['ip_sources'][$k] = array_merge($cfg['ip_sources'][$k] ?? [], ['enabled' => (bool)($v['enabled'] ?? true)]);
            }
            if (isset($input['oauth'])) $cfg['oauth'] = ['apiurl' => rtrim($input['oauth']['apiurl'] ?? '', '/') . '/', 'appid' => $input['oauth']['appid'] ?? '', 'appkey' => $input['oauth']['appkey'] ?? ''];

            file_put_contents(__DIR__ . '/../config.json', json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $result['success'] = true; return;

        case 'admin_files':
            toolbox_session_start(); if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = 'permission denied'; return; }
            $pdo = dbConnect(); if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS `files` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `size` BIGINT NOT NULL DEFAULT 0, `type` VARCHAR(100), `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                try { $pdo->exec("ALTER TABLE files ADD COLUMN user_id INT DEFAULT 0"); } catch (Exception $e) {}
                $stmt = $pdo->query("SELECT f.id, f.name, f.size, f.type, f.uploaded_at, COALESCE(f.user_id,0) as user_id, u.username FROM files f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.id DESC");
                $dbFiles = $stmt->fetchAll(); $dbMap = [];
                foreach ($dbFiles as $f) { $dbMap[$f['name']] = $f; }
                $uploadDir = __DIR__ . '/../uploads/';
                $files = [];
                if (is_dir($uploadDir)) {
                    foreach (scandir($uploadDir) as $fn) {
                        if ($fn === '.' || $fn === '..') continue;
                        $fp = $uploadDir . $fn;
                        if (!is_file($fp)) continue;
                        if (isset($dbMap[$fn])) {
                            $files[] = $dbMap[$fn];
                        } else {
                            $files[] = ['id' => 0, 'name' => $fn, 'size' => filesize($fp), 'type' => '', 'uploaded_at' => date('Y-m-d H:i:s', filemtime($fp)), 'user_id' => 0, 'username' => '未知'];
                        }
                    }
                }
                usort($files, function($a, $b) { return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']); });
                $result['data'] = $files; $result['success'] = true;
            } catch (Exception $e) { $result['error'] = '查询失败: ' . $e['message']; }
            return;
    }
}
