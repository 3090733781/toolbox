<?php
function handle_user(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'user_register':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $user = trim($input['username'] ?? ''); $pass = $input['password'] ?? '';
            if (!$user || strlen($user) < 2) { $result['error'] = '用户名至少2位'; return; }
            if (strtolower($user) === 'admin') { $result['error'] = '该用户名被保留'; return; }
            if (!$pass || strlen($pass) < 4) { $result['error'] = '密码至少4位'; return; }
            $pdo = dbConnect(); if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
            $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (`id` INT AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(50) NOT NULL UNIQUE, `password_hash` VARCHAR(255) NOT NULL, `role` VARCHAR(20) DEFAULT 'user', `api_key` VARCHAR(64) DEFAULT NULL, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?"); $stmt->execute([$user]);
            if ($stmt->fetch()) { $result['error'] = '用户名已存在'; return; }
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $isFirst = $stmt->fetchColumn() == 0; $role = $isFirst ? 'admin' : 'user';
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)"); $stmt->execute([$user, $hash, $role]);
            $result['success'] = true; $result['data'] = ['username' => $user, 'role' => $role, 'is_first' => $isFirst];
            return;

        case 'user_login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $user = trim($input['username'] ?? ''); $pass = $input['password'] ?? '';
            if (!$user || !$pass) { $result['error'] = '请输入用户名和密码'; return; }
            $pdo = dbConnect(); if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?"); $stmt->execute([$user]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($pass, $row['password_hash'])) { $result['error'] = '用户名或密码错误'; return; }
            @session_start(); $_SESSION['admin'] = true; $_SESSION['user_id'] = $row['id']; $_SESSION['username'] = $row['username']; $_SESSION['role'] = $row['role'];
            $result['success'] = true; $result['data'] = ['username' => $row['username'], 'role' => $row['role']];
            return;

        case 'user_logout':
            @session_start(); $_SESSION = []; session_destroy(); $result['success'] = true; return;

        case 'user_info':
            @session_start(); if (empty($_SESSION['admin'])) { $result['error'] = '未登录'; return; }
            $pdo = dbConnect(); if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
            $stmt = $pdo->prepare("SELECT id, username, role, api_key, created_at FROM users WHERE id = ?"); $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch(); if (!$row) { $result['error'] = '用户不存在'; return; }
            $result['success'] = true; $result['data'] = $row; return;

        case 'user_api_key':
            @session_start(); if (empty($_SESSION['admin'])) { $result['error'] = '未登录'; return; }
            $pdo = dbConnect(); if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
            try { $pdo->exec("ALTER TABLE users ADD COLUMN api_key VARCHAR(64) DEFAULT NULL"); } catch (Exception $e) {}
            $input = json_decode(file_get_contents('php://input'), true);
            $regenerate = ($input['regenerate'] ?? false) || ($_GET['regenerate'] ?? false);
            if ($regenerate) { $key = bin2hex(random_bytes(16)); $stmt = $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?"); $stmt->execute([$key, $_SESSION['user_id']]); }
            else {
                $stmt = $pdo->prepare("SELECT api_key FROM users WHERE id = ?"); $stmt->execute([$_SESSION['user_id']]); $row = $stmt->fetch();
                if (!$row || !$row['api_key']) { $key = bin2hex(random_bytes(16)); $stmt = $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?"); $stmt->execute([$key, $_SESSION['user_id']]); }
                else { $key = $row['api_key']; }
            }
            $result['success'] = true; $result['data'] = ['api_key' => $key]; return;

        case 'user_list':
            @session_start(); if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $pdo = dbConnect(); if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
            $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id"); $result['success'] = true; $result['data'] = $stmt->fetchAll(); return;

        case 'user_delete':
            @session_start(); if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $id = intval($_GET['id'] ?? 0); if ($id <= 0) { $result['error'] = '无效用户'; return; }
            if ($id === $_SESSION['user_id']) { $result['error'] = '不能删除自己'; return; }
            $pdo = dbConnect(); if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?"); $stmt->execute([$id]);
            $result['success'] = true; return;
    }
}
