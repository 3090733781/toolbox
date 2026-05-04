<?php
/**
 * API Key 管理接口
 * 支持：创建/列表/删除/启禁/更新/日志/统计
 */
function ensureApiTables($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `api_keys` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL DEFAULT 'Default',
        `api_key` VARCHAR(64) NOT NULL UNIQUE,
        `permissions` VARCHAR(500) DEFAULT '*',
        `rate_limit` INT DEFAULT 60 COMMENT '每分钟最大调用次数，0=不限',
        `status` TINYINT DEFAULT 1 COMMENT '1=启用 0=禁用',
        `expires_at` DATETIME DEFAULT NULL COMMENT 'NULL=永不过期',
        `used_count` BIGINT DEFAULT 0,
        `last_used_at` DATETIME DEFAULT NULL,
        `last_used_ip` VARCHAR(45) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (`user_id`),
        INDEX idx_key (`api_key`),
        INDEX idx_status (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `api_logs` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `api_key_id` INT DEFAULT NULL,
        `user_id` INT DEFAULT NULL,
        `source` VARCHAR(50) NOT NULL,
        `ip` VARCHAR(45) DEFAULT NULL,
        `user_agent` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(10) DEFAULT 'ok',
        `error` VARCHAR(500) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_key (`api_key_id`),
        INDEX idx_source (`source`),
        INDEX idx_time (`created_at`),
        INDEX idx_user (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function handle_apikey(&$result, $source, $query, $cfg, $key) {
    @session_start();
    $pdo = dbConnect();
    if (!$pdo) { $result['error'] = '数据库连接失败'; return; }
    ensureApiTables($pdo);

    $userId = $_SESSION['user_id'] ?? 0;
    $role = $_SESSION['role'] ?? '';

    switch ($source) {

        // ====== 用户自己的 Key 管理 ======

        case 'apikey_list':
            if (!$userId) { $result['error'] = '未登录'; return; }
            $stmt = $pdo->prepare("SELECT id, name, api_key, permissions, rate_limit, status, expires_at, used_count, last_used_at, last_used_ip, created_at FROM api_keys WHERE user_id = ? ORDER BY id DESC");
            $stmt->execute([$userId]);
            $result['success'] = true;
            $result['data'] = $stmt->fetchAll();
            return;

        case 'apikey_create':
            if (!$userId) { $result['error'] = '未登录'; return; }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $name = trim($input['name'] ?? 'Default');
            $permissions = trim($input['permissions'] ?? '*');
            $rateLimit = max(0, intval($input['rate_limit'] ?? 60));
            $expiresAt = !empty($input['expires_at']) ? $input['expires_at'] : null;

            if (!$name) { $result['error'] = '请输入 Key 名称'; return; }

            // 限制每用户最多 10 个 key
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE user_id = ?");
            $stmt->execute([$userId]);
            if ((int)$stmt->fetchColumn() >= 10) { $result['error'] = '每个用户最多创建 10 个 API Key'; return; }

            $newKey = 'cx_' . bin2hex(random_bytes(20));
            $stmt = $pdo->prepare("INSERT INTO api_keys (user_id, name, api_key, permissions, rate_limit, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $name, $newKey, $permissions, $rateLimit, $expiresAt]);

            $result['success'] = true;
            $result['data'] = [
                'id' => (int)$pdo->lastInsertId(),
                'name' => $name,
                'api_key' => $newKey,
                'permissions' => $permissions,
                'rate_limit' => $rateLimit,
                'expires_at' => $expiresAt,
            ];
            return;

        case 'apikey_delete':
            if (!$userId) { $result['error'] = '未登录'; return; }
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) { $result['error'] = '无效 ID'; return; }
            $stmt = $pdo->prepare("DELETE FROM api_keys WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            if ($stmt->rowCount() === 0) { $result['error'] = '未找到该 Key 或无权删除'; return; }
            $result['success'] = true;
            return;

        case 'apikey_toggle':
            if (!$userId) { $result['error'] = '未登录'; return; }
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) { $result['error'] = '无效 ID'; return; }
            $stmt = $pdo->prepare("UPDATE api_keys SET status = IF(status=1, 0, 1) WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            if ($stmt->rowCount() === 0) { $result['error'] = '未找到该 Key'; return; }
            $result['success'] = true;
            return;

        case 'apikey_update':
            @session_start();
            if (!$userId && empty($_SESSION['role'])) { $result['error'] = '未登录'; return; }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            if ($id <= 0) { $result['error'] = '无效 ID'; return; }
            $sets = []; $params = [];
            if (isset($input['name'])) { $sets[] = 'name = ?'; $params[] = trim($input['name']); }
            if (isset($input['permissions'])) { $sets[] = 'permissions = ?'; $params[] = trim($input['permissions']); }
            if (isset($input['rate_limit'])) { $sets[] = 'rate_limit = ?'; $params[] = max(0, intval($input['rate_limit'])); }
            if (array_key_exists('expires_at', $input)) { $sets[] = 'expires_at = ?'; $params[] = $input['expires_at'] ?: null; }
            if (empty($sets)) { $result['error'] = '无更新内容'; return; }
            $params[] = $id;
            // 管理员可编辑任意 Key，普通用户只能编辑自己的
            $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
            if ($isAdmin) {
                $stmt = $pdo->prepare("UPDATE api_keys SET " . implode(', ', $sets) . " WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE api_keys SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ?");
                $params[] = $userId;
            }
            $stmt->execute($params);
            $result['success'] = true;
            return;

        case 'apikey_logs':
            if (!$userId) { $result['error'] = '未登录'; return; }
            $keyId = intval($_GET['key_id'] ?? 0);
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 50)));
            $offset = ($page - 1) * $limit;

            if ($keyId) {
                // 验证 key 属于当前用户
                $stmt = $pdo->prepare("SELECT id FROM api_keys WHERE id = ? AND user_id = ?");
                $stmt->execute([$keyId, $userId]);
                if (!$stmt->fetch()) { $result['error'] = '无权查看'; return; }
                $stmt = $pdo->prepare("SELECT source, ip, status, error, created_at FROM api_logs WHERE api_key_id = ? ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->execute([$keyId, $limit, $offset]);
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM api_logs WHERE api_key_id = ?");
                $countStmt->execute([$keyId]);
            } else {
                // 查看自己所有 key 的日志
                $stmt = $pdo->prepare("SELECT l.source, l.ip, l.status, l.error, l.created_at, k.name as key_name FROM api_logs l LEFT JOIN api_keys k ON l.api_key_id = k.id WHERE l.user_id = ? ORDER BY l.id DESC LIMIT ? OFFSET ?");
                $stmt->execute([$userId, $limit, $offset]);
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM api_logs WHERE user_id = ?");
                $countStmt->execute([$userId]);
            }
            $result['success'] = true;
            $result['data'] = [
                'logs' => $stmt->fetchAll(),
                'total' => (int)$countStmt->fetchColumn(),
                'page' => $page,
                'limit' => $limit,
            ];
            return;

        case 'apikey_stats':
            if (!$userId) { $result['error'] = '未登录'; return; }
            // 总调用次数
            $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(status='ok') as success, SUM(status='fail') as fail FROM api_logs WHERE user_id = ?");
            $stmt->execute([$userId]);
            $totals = $stmt->fetch();
            // 今日调用
            $stmt = $pdo->prepare("SELECT COUNT(*) as today_total, SUM(status='ok') as today_ok FROM api_logs WHERE user_id = ? AND DATE(created_at) = CURDATE()");
            $stmt->execute([$userId]);
            $today = $stmt->fetch();
            // 按接口统计 Top 10
            $stmt = $pdo->prepare("SELECT source, COUNT(*) as cnt FROM api_logs WHERE user_id = ? GROUP BY source ORDER BY cnt DESC LIMIT 10");
            $stmt->execute([$userId]);
            $topSources = $stmt->fetchAll();
            // Key 数量
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE user_id = ?");
            $stmt->execute([$userId]);
            $keyCount = (int)$stmt->fetchColumn();

            $result['success'] = true;
            $result['data'] = [
                'total_calls' => (int)($totals['total'] ?? 0),
                'success_calls' => (int)($totals['success'] ?? 0),
                'fail_calls' => (int)($totals['fail'] ?? 0),
                'today_calls' => (int)($today['today_total'] ?? 0),
                'today_success' => (int)($today['today_ok'] ?? 0),
                'key_count' => $keyCount,
                'top_sources' => $topSources,
            ];
            return;

        // ====== 管理员接口 ======

        case 'apikey_list_all':
            $stmt = $pdo->query("SELECT k.*, u.username FROM api_keys k LEFT JOIN users u ON k.user_id = u.id ORDER BY k.id DESC");
            $result['success'] = true;
            $result['data'] = $stmt->fetchAll();
            return;

        case 'apikey_logs_all':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 50)));
            $offset = ($page - 1) * $limit;
            $stmt = $pdo->prepare("SELECT l.*, k.name as key_name, u.username FROM api_logs l LEFT JOIN api_keys k ON l.api_key_id = k.id LEFT JOIN users u ON l.user_id = u.id ORDER BY l.id DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $countStmt = $pdo->query("SELECT COUNT(*) FROM api_logs");
            $result['success'] = true;
            $result['data'] = [
                'logs' => $stmt->fetchAll(),
                'total' => (int)$countStmt->fetchColumn(),
                'page' => $page,
                'limit' => $limit,
            ];
            return;

        case 'apikey_admin_create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $targetUserId = intval($input['user_id'] ?? 0);
            if ($targetUserId <= 0) { $result['error'] = '请指定用户 ID'; return; }
            $name = trim($input['name'] ?? 'Admin Created');
            $permissions = trim($input['permissions'] ?? '*');
            $rateLimit = max(0, intval($input['rate_limit'] ?? 60));
            $expiresAt = !empty($input['expires_at']) ? $input['expires_at'] : null;
            $newKey = 'cx_' . bin2hex(random_bytes(20));
            $stmt = $pdo->prepare("INSERT INTO api_keys (user_id, name, api_key, permissions, rate_limit, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$targetUserId, $name, $newKey, $permissions, $rateLimit, $expiresAt]);
            $result['success'] = true;
            $result['data'] = ['id' => (int)$pdo->lastInsertId(), 'api_key' => $newKey];
            return;

        case 'apikey_admin_delete':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) { $result['error'] = '无效 ID'; return; }
            $pdo->prepare("DELETE FROM api_keys WHERE id = ?")->execute([$id]);
            $result['success'] = true;
            return;

        case 'apikey_admin_toggle':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) { $result['error'] = '无效 ID'; return; }
            $pdo->prepare("UPDATE api_keys SET status = IF(status=1, 0, 1) WHERE id = ?")->execute([$id]);
            $result['success'] = true;
            return;
    }
}
