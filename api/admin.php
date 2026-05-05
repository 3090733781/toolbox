<?php
function handle_admin(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'admin_login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $pwd = $input['password'] ?? ''; $hash = $cfg['password_hash'] ?? '';
            if (!$hash) { $result['success'] = true; $result['data'] = ['need_setup' => true]; return; }
            if (password_verify($pwd, $hash)) { @session_start(); $_SESSION['admin'] = true; $_SESSION['role'] = 'admin'; $result['success'] = true; }
            else { $result['error'] = '密码错误'; }
            return;

        case 'admin_check':
            @session_start(); $result['success'] = !empty($_SESSION['admin']); return;

        case 'admin_logout':
            @session_start(); $_SESSION = []; session_destroy(); $result['success'] = true; return;

        case 'admin_save':
            @session_start(); $cfg = loadConfig();
            $rawBody = file_get_contents('php://input'); $input = json_decode($rawBody, true);
            if (!$input) { $result['error'] = '无效数据'; return; }
            $needAuth = !(empty($cfg['password_hash']) && !empty($input['password'] ?? ''));
            if ($needAuth && (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')) { $result['error'] = '无权限'; return; }
            if (!empty($input['amap_key']) && $_SESSION['role'] !== 'admin') { $result['error'] = '仅管理员可修改配置'; return; }
            if (!empty($input['password'])) {
                $cfg['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
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
            @session_start(); if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
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
            } catch (Exception $e) { $result['error'] = '查询失败: ' . $e->getMessage(); }
            return;
    }
}
