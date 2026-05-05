<?php
$lockFile = __DIR__ . '/install.lock';
if (file_exists($lockFile)) { header('Location: ../'); exit; }
$step = intval($_GET['step'] ?? 1);
$err = '';
$success = '';
$vFile = __DIR__ . '/../version.json';
$currentVer = (file_exists($vFile) ? json_decode(file_get_contents($vFile), true) : [])['version'] ?? '1.0.0';

function checkEnv() {
    $checks = [];
    $checks[] = ['name' => 'PHP 版本', 'req' => '≥ 7.4', 'got' => PHP_VERSION, 'pass' => version_compare(PHP_VERSION, '7.4.0', '>='), 'required' => true];
    $checks[] = ['name' => 'cURL 扩展', 'req' => '必需', 'got' => extension_loaded('curl') ? '✓' : '✗', 'pass' => extension_loaded('curl'), 'required' => true];
    $checks[] = ['name' => 'PDO_MySQL', 'req' => '必需', 'got' => extension_loaded('pdo_mysql') ? '✓' : '✗', 'pass' => extension_loaded('pdo_mysql'), 'required' => true];
    $checks[] = ['name' => 'JSON 扩展', 'req' => '必需', 'got' => extension_loaded('json') ? '✓' : '✗', 'pass' => extension_loaded('json'), 'required' => true];
    $checks[] = ['name' => 'session 扩展', 'req' => '必需', 'got' => extension_loaded('session') ? '✓' : '✗', 'pass' => extension_loaded('session'), 'required' => true];
    $checks[] = ['name' => 'fileinfo', 'req' => '推荐', 'got' => extension_loaded('fileinfo') ? '✓' : '✗', 'pass' => extension_loaded('fileinfo'), 'required' => false];
    $uploads = __DIR__ . '/../uploads';
    $checks[] = ['name' => 'uploads 权限', 'req' => '可写', 'got' => is_writable($uploads) ? '✓' : '✗', 'pass' => is_writable($uploads), 'required' => true];
    $config = __DIR__ . '/../config.json';
    $checks[] = ['name' => 'config 权限', 'req' => '可写', 'got' => is_writable($config) || !file_exists($config) ? '✓' : '✗', 'pass' => is_writable($config) || !file_exists($config), 'required' => true];
    return $checks;
}

function dbHasTables($pdo, $dbname) {
    $pdo->exec("USE `{$dbname}`");
    $stmt = $pdo->query("SHOW TABLES");
    return count($stmt->fetchAll()) > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 3) {
    $host = $_POST['host'] ?? '127.0.0.1';
    $port = intval($_POST['port'] ?? 3306);
    $user = $_POST['user'] ?? 'root';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'cx_toolbox';
    $force = !empty($_POST['force']);
    try {
        $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $hasTables = dbHasTables($pdo, $dbname);
        if ($hasTables && !$force) {
            $err = '数据库「' . htmlspecialchars($dbname) . '」已存在数据表，如需覆盖请勾选"强制安装"';
        }
        if (!$err && $hasTables) {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $t) $pdo->exec("DROP TABLE IF EXISTS `{$t}`");
        }
        if (!$err) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS `files` (`id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `size` BIGINT NOT NULL DEFAULT 0, `type` VARCHAR(100), `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (`id` INT AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(50) NOT NULL UNIQUE, `password_hash` VARCHAR(255) NOT NULL, `role` VARCHAR(20) DEFAULT 'user', `api_key` VARCHAR(64) DEFAULT NULL, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $stmt = $pdo->prepare("INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('db_version', '1.0.0')");
            $stmt->execute();
            $hash = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT IGNORE INTO `users` (`username`, `password_hash`, `role`) VALUES ('admin', ?, 'admin')");
            $stmt->execute([$hash]);
            $cfg = json_decode(file_get_contents(__DIR__ . '/../config.json'), true) ?: [];
            $cfg['db'] = ['host' => $host, 'port' => $port, 'user' => $user, 'pass' => $pass, 'name' => $dbname];
            $cfg['installed'] = true;
            file_put_contents(__DIR__ . '/../config.json', json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            file_put_contents(__DIR__ . '/install.lock', '安装时间：' . date('Y-m-d H:i:s'));
            $success = '安装完成！';
            $step = 4;
        }
    } catch (Exception $e) {
        $err = '数据库连接失败：' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $content = $_POST['changelog'] ?? '';
    if ($content) file_put_contents(__DIR__ . '/../CHANGELOG.md', $content);
    elseif (!file_exists(__DIR__ . '/../CHANGELOG.md')) file_put_contents(__DIR__ . '/../CHANGELOG.md', "# 更新日志\n\n## {$currentVer}\n- \n");
    header('Location: ?step=3');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>工具箱 - 安装向导</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wizard{background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.15);max-width:680px;width:100%;overflow:hidden}
.header{background:linear-gradient(135deg,#4f6af5,#764ba2);color:#fff;padding:36px 40px 28px;text-align:center}
.header h1{font-size:26px;font-weight:800;margin-bottom:6px}
.header p{font-size:14px;opacity:.8}
.progress{display:flex;padding:0 40px;background:#f8f9fb;border-bottom:1px solid #e8ecf1}
.progress-step{flex:1;text-align:center;padding:16px 0;font-size:13px;font-weight:600;color:#999;position:relative}
.progress-step.active{color:#4f6af5}
.progress-step.done{color:#22c55e}
.progress-step .num{display:inline-flex;width:28px;height:28px;border-radius:50%;align-items:center;justify-content:center;font-size:13px;margin-right:6px;background:#e8ecf1;color:#999;font-weight:700}
.progress-step.active .num{background:#4f6af5;color:#fff}
.progress-step.done .num{background:#22c55e;color:#fff}
.progress-step::after{content:'';position:absolute;top:50%;right:-50%;width:100%;height:2px;background:#e8ecf1;z-index:0}
.progress-step:last-child::after{display:none}
.progress-step.done::after{background:#22c55e}
.body{padding:36px 40px}
.body h2{font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:6px}
.body .sub{font-size:13px;color:#888;margin-bottom:24px}
.check-grid{display:grid;gap:10px}
.check-item{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#f8f9fb;border-radius:10px;border:1px solid #e8ecf1}
.check-item .left{display:flex;align-items:center;gap:10px}
.check-item .name{font-size:14px;font-weight:600;color:#333}
.check-item .req{font-size:12px;color:#999}
.check-item .got{font-size:13px;font-weight:600}
.check-item .got.pass{color:#22c55e}
.check-item .got.fail{color:#e74c3c}
.check-item .icon{font-size:18px}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:6px}
.form-group input,.form-group textarea{width:100%;padding:12px 16px;border:2px solid #e0e4ea;border-radius:10px;font-size:14px;outline:none;transition:border-color .2s}
.form-group input:focus,.form-group textarea:focus{border-color:#4f6af5}
.form-group textarea{min-height:180px;font-family:monospace;font-size:13px;resize:vertical;line-height:1.6}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-hint{font-size:12px;color:#999;margin-top:4px}
.btn{padding:14px 32px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:8px}
.btn-primary{background:linear-gradient(135deg,#4f6af5,#764ba2);color:#fff}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(79,106,245,.35)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-outline{background:#fff;color:#666;border:2px solid #e0e4ea}
.btn-outline:hover{border-color:#4f6af5;color:#4f6af5}
.actions{display:flex;justify-content:space-between;align-items:center;margin-top:28px;padding-top:24px;border-top:1px solid #e8ecf1}
.err{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px}
.warn-box{background:#fff8e1;border:1px solid #ffe082;border-radius:10px;padding:16px;margin-bottom:20px}
.warn-box.error{border-color:#fecaca;background:#fef2f2}
.warn-box.error label{color:#dc2626!important}
.success-msg{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;font-size:14px;margin-bottom:20px;text-align:center}
.success-msg .big{font-size:48px;margin-bottom:12px;display:block}
.success-msg a{color:#4f6af5;text-decoration:none;font-weight:600}
.loading{text-align:center;padding:24px;color:#888;font-size:14px}
.spinner{width:24px;height:24px;border:3px solid #e8ecf1;border-top-color:#4f6af5;border-radius:50%;animation:spin .7s linear infinite;display:inline-block;vertical-align:middle;margin-right:8px}
@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:640px){.header{padding:28px 20px 20px}.header h1{font-size:22px}.body{padding:24px 20px}.progress{padding:0 20px}.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="wizard">
<div class="header">
<h1>🚀 工具箱安装向导</h1>
<p>快速完成环境配置与数据库安装</p>
</div>

<div class="progress">
<div class="progress-step <?= $step > 1 ? 'done' : 'active' ?>"><span class="num"><?= $step > 1 ? '✓' : '1' ?></span> 环境检测</div>
<div class="progress-step <?= $step > 2 ? 'done' : ($step == 2 ? 'active' : '') ?>"><span class="num"><?= $step > 2 ? '✓' : '2' ?></span> 更新日志</div>
<div class="progress-step <?= $step > 3 ? 'done' : ($step == 3 ? 'active' : '') ?>"><span class="num"><?= $step > 3 ? '✓' : '3' ?></span> 数据库安装</div>
</div>

<div class="body">
<?php if ($err): ?><div class="err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
<?php if ($success): ?><div class="success-msg"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<?php if ($step === 1): ?>
<h2>🔍 环境检测</h2>
<p class="sub">检测服务器环境是否满足运行要求</p>
<div class="check-grid">
<?php $allPass = true; $hasWarn = false; foreach (checkEnv() as $c): if ($c['required']) { $allPass = $allPass && $c['pass']; } elseif (!$c['pass']) { $hasWarn = true; } ?>
<div class="check-item">
<div class="left">
<span class="icon"><?= $c['pass'] ? '✅' : '❌' ?></span>
<div><div class="name"><?= htmlspecialchars($c['name']) ?></div><div class="req">要求：<?= htmlspecialchars($c['req']) ?></div></div>
</div>
<span class="got <?= $c['pass'] ? 'pass' : 'fail' ?>"><?= htmlspecialchars($c['got']) ?></span>
</div>
<?php endforeach; ?>
</div>
<div class="actions">
<span style="font-size:13px;color:#888"><?= $allPass ? ($hasWarn ? '⚠️ 环境通过（有可选警告）' : '✅ 环境通过') : '❌ 存在必选项不满足，请修复后继续' ?></span>
<a href="?step=2" class="btn btn-primary <?= !$allPass ? 'disabled' : '' ?>" <?= !$allPass ? 'onclick="return false"' : '' ?>>下一步 →</a>
</div>

<?php elseif ($step === 2): ?>
<form method="post">
<h2>📝 更新日志</h2>
<p class="sub">查看本版本的更新内容</p>
<?php $md = file_exists(__DIR__ . '/../CHANGELOG.md') ? file_get_contents(__DIR__ . '/../CHANGELOG.md') : ''; ?>
<div class="form-group">
<textarea name="changelog" placeholder="# 更新日志&#10;&#10;## <?= htmlspecialchars($currentVer) ?>&#10;- " readonly style="background:#f8f9fb;color:#666;cursor:default"><?= htmlspecialchars($md) ?></textarea>
</div>
<div class="actions">
<a href="?step=1" class="btn btn-outline">← 上一步</a>
<button type="submit" class="btn btn-primary">下一步 →</button>
</div>
</form>

<?php elseif ($step === 3): ?>
<form method="post">
<h2>🗄️ 数据库配置</h2>
<p class="sub">填写 MySQL 数据库信息，系统将自动创建数据库和表</p>
<div class="form-row">
<div class="form-group">
<label>数据库主机</label>
<input type="text" name="host" value="<?= htmlspecialchars($_POST['host'] ?? '127.0.0.1') ?>" placeholder="127.0.0.1">
</div>
<div class="form-group">
<label>端口</label>
<input type="number" name="port" value="<?= htmlspecialchars($_POST['port'] ?? '3306') ?>" placeholder="3306">
</div>
</div>
<div class="form-row">
<div class="form-group">
<label>用户名</label>
<input type="text" name="user" value="<?= htmlspecialchars($_POST['user'] ?? 'root') ?>" placeholder="root">
</div>
<div class="form-group">
<label>密码</label>
<input type="password" name="pass" value="<?= htmlspecialchars($_POST['pass'] ?? '') ?>" placeholder="数据库密码">
</div>
</div>
<div class="form-group">
<label>数据库名</label>
<input type="text" name="dbname" value="<?= htmlspecialchars($_POST['dbname'] ?? 'cx_toolbox') ?>" placeholder="cx_toolbox">
<div class="form-hint">如果数据库不存在，系统会自动创建</div>
</div>
<div class="warn-box <?= strpos($err, '已存在数据表') !== false ? 'error' : '' ?>">
<label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:#795548">
<input type="checkbox" name="force" value="1" style="width:18px;height:18px;cursor:pointer" <?= !empty($_POST['force']) ? 'checked' : '' ?>>
<span>⚠️ 强制安装 — 清空数据库所有已有数据表</span>
</label>
</div>
<div class="actions">
<a href="?step=2" class="btn btn-outline">← 上一步</a>
<button type="submit" class="btn btn-primary" onclick="this.innerHTML='<span class=spinner></span>安装中...';this.disabled=true;this.form.submit()">⚡ 开始安装</button>
</div>
</form>

<?php elseif ($step === 4): ?>
<div class="success-msg" style="padding:32px 20px">
<span class="big">🎉</span>
<h2 style="font-size:20px;margin-bottom:8px;color:#16a34a">安装完成！</h2>
<p style="font-size:14px;margin-bottom:20px">工具箱已准备就绪，可以开始使用了</p>
<div style="background:#f0f4ff;border:1px solid #c7d2fe;border-radius:10px;padding:16px;margin:0 auto 20px;max-width:320px;text-align:left;font-size:13px">
<div style="font-weight:700;color:#4f6af5;margin-bottom:10px">🔑 默认管理员账号</div>
<div style="display:flex;justify-content:space-between;padding:4px 0"><span style="color:#666">用户名</span><span style="font-weight:600">admin</span></div>
<div style="display:flex;justify-content:space-between;padding:4px 0"><span style="color:#666">密码</span><span style="font-weight:600">123456</span></div>
<div style="font-size:11px;color:#999;margin-top:8px;border-top:1px solid #e0e7ff;padding-top:8px">首次登录后请及时修改密码</div>
</div>
<a href="../admin/" class="btn btn-primary">进入后台 →</a>
<a href="../" class="btn btn-outline" style="margin-left:8px">返回首页</a>
</div>
<?php endif; ?>
</div>
</div>

</body>
</html>
