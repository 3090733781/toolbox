<?php
error_reporting(0);
require_once __DIR__ . '/../../includes/session.php';
toolbox_session_start();
@header('Content-Type: text/html; charset=UTF-8');

include 'Oauth.config.php';
include 'Oauth.class.php';

function db() {
    $cfg = json_decode(file_get_contents(__DIR__ . '/../../config.json'), true);
    $db = $cfg['db'] ?? [];
    if (empty($db)) return null;
    try {
        $pdo = new PDO("mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        try { $pdo->exec("ALTER TABLE users ADD COLUMN social_uid VARCHAR(100) DEFAULT NULL"); } catch (Exception $e) {}
        try { $pdo->exec("ALTER TABLE users ADD COLUMN social_type VARCHAR(20) DEFAULT NULL"); } catch (Exception $e) {}
        return $pdo;
    } catch (Exception $e) { return null; }
}

$type = isset($_GET['type']) ? $_GET['type'] : 'qq';

if ($_GET['code']) {
    if ($_GET['state'] != $_SESSION['Oauth_state']) {
        exit("The state does not match. You may be a victim of CSRF.");
    }
    $Oauth = new Oauth($Oauth_config);
    $arr = $Oauth->callback();
    if (isset($arr['code']) && $arr['code'] == 0) {
        $social_uid = $arr['social_uid'];
        $pdo = db();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE social_uid = ? AND social_type = ?");
            $stmt->execute([$social_uid, $type]);
            $user = $stmt->fetch();
            if (!$user) {
                $username = $arr['nickname'] ?: $type . '_' . substr($social_uid, -6);
                $i = 1; $base = $username;
                while (true) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if (!$stmt->fetch()) break;
                    $username = $base . $i;
                    $i++;
                }
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, social_uid, social_type) VALUES (?, '', 'user', ?, ?)");
                $stmt->execute([$username, $social_uid, $type]);
                $userId = $pdo->lastInsertId();
            } else {
                $userId = $user['id'];
                $username = $user['username'];
            }
            $_SESSION['admin'] = true;
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user ? $user['role'] : 'user';
        }
        $jsUrl = json_encode(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/');
        exit("<script>window.location.href={$jsUrl}</script>");
    } elseif (isset($arr['code'])) {
        exit('登录失败：' . $arr['msg']);
    } else {
        exit('获取登录数据失败');
    }
} else {
    $Oauth = new Oauth($Oauth_config);
    $arr = $Oauth->login($type);
    if (isset($arr['code']) && $arr['code'] == 0) {
        exit("<script>window.location.href='{$arr['url']}'</script>");
    } elseif (isset($arr['code'])) {
        exit('登录接口返回：' . $arr['msg']);
    } else {
        exit('获取登录地址失败');
    }
}
