<?php
function handle_category(&$result, $source, $query, $cfg, $key) {
    $pdo = dbConnect();
    if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL, `sort` INT DEFAULT 0, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    try { $pdo->exec("ALTER TABLE categories ADD COLUMN `mode` VARCHAR(10) DEFAULT 'list'"); } catch (Exception $e) {}
    switch ($source) {
        case 'cat_list':
            $mode = $_GET['mode'] ?? '';
            if ($mode) {
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE mode = ? ORDER BY sort, id");
                $stmt->execute([$mode]);
            } else {
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY sort, id");
            }
            $result['success'] = true; $result['data'] = $stmt->fetchAll();
            return;

        case 'cat_add':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $name = trim($input['name'] ?? '');
            $mode = $input['mode'] ?? 'list';
            if (!$name) { $result['error'] = '请输入分类名称'; return; }
            $stmt = $pdo->prepare("INSERT INTO categories (name, mode) VALUES (?, ?)");
            $stmt->execute([$name, $mode]);
            $result['success'] = true; $result['data'] = ['id' => $pdo->lastInsertId()];
            return;

        case 'cat_delete':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) { $result['error'] = '无效ID'; return; }
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $result['success'] = true;
            return;

        case 'cat_mode':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $mode = $input['mode'] ?? 'list';
            if (!in_array($mode, ['list','top'])) { $result['error'] = '无效模式'; return; }
            $stmt = $pdo->prepare("UPDATE categories SET mode = ? WHERE id = ?");
            $stmt->execute([$mode, $id]);
            $result['success'] = true;
            return;

        case 'cat_batch_mode':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $mode = $input['mode'] ?? 'list';
            if (!in_array($mode, ['list','top'])) { $result['error'] = '无效模式'; return; }
            $ids = $input['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) { $result['error'] = '请选择分类'; return; }
            $ids = array_map('intval', $ids);
            $ids = array_values(array_filter($ids, function($id) { return $id > 0; }));
            if (empty($ids)) { $result['error'] = '无有效分类ID'; return; }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE categories SET mode = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$mode], $ids));
            $result['success'] = true;
            return;
    }
}
