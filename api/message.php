<?php
function handle_message(&$result, $source, $query, $cfg, $key) {
    $pdo = dbConnect();
    if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
    $pdo->exec("CREATE TABLE IF NOT EXISTS `messages` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(50) DEFAULT '访客', `content` TEXT NOT NULL, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    switch ($source) {
        case 'msg_add':
            $u = authUser();
            if (!$u) { $result['error'] = '请先登录'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $name = trim($input['name'] ?? $u['username'] ?? '访客');
            $content = trim($input['content'] ?? '');
            if (!$content) { $result['error'] = '请输入留言内容'; return; }
            $stmt = $pdo->prepare("INSERT INTO messages (name, content) VALUES (?, ?)");
            $stmt->execute([$name, $content]);
            $result['success'] = true; $result['data'] = ['id' => $pdo->lastInsertId()];
            return;

        case 'msg_list':
            $stmt = $pdo->query("SELECT * FROM messages ORDER BY id DESC");
            $result['success'] = true; $result['data'] = $stmt->fetchAll();
            return;

        case 'msg_delete':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) { $result['error'] = '无效ID'; return; }
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$id]);
            $result['success'] = true;
            return;
    }
}
