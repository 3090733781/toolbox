<?php @session_start();
$cfgFile = __DIR__ . '/../config.json';
if (file_exists($cfgFile)) { $cfg = json_decode(file_get_contents($cfgFile), true); if (!file_exists(__DIR__ . '/../install/install.lock')) { header('Location: ../install/'); exit; } } else { header('Location: ../install/'); exit; }

// 退出登录
$p = $_GET['p'] ?? 'dashboard';
if ($p === 'logout') { $_SESSION = []; session_destroy(); header('Location: ../'); exit; }

// 管理员跳转 admin 目录
$role = $_SESSION['role'] ?? '';
if ($role === 'admin') { header('Location: ../admin/'); exit; }

// 未登录拦截
if (empty($_SESSION['admin'])) { header('Location: ../'); exit; }

$pages = ['dashboard','my-files'];
$pageTitle = ['dashboard'=>'我的主页','my-files'=>'我的文件'];
if (!in_array($p, $pages)) $p = 'dashboard';
?><!DOCTYPE html>
<html><head><meta charset="utf-8"><title><?= $pageTitle[$p] ?> - 工具箱</title>
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<link rel="stylesheet" href="../admin/css/admin.css?v=2.4">
</head>
<body>
<div class="header"><div class="logo">⚙ 工具箱 <span>v<span id="ver"></span></span></div>
<div class="header-right"><span class="user" id="userLabel"></span><a href="../" target="_blank">网站首页</a><a href="?p=logout">退出登录</a></div></div>
<div class="sidebar">
<div class="menu-group"><div class="menu-group-title">我的</div>
<a class="menu-item <?= $p==='dashboard'?'active':'' ?>" href="?p=dashboard"><span class="icon">🏠</span>我的主页</a>
<a class="menu-item <?= $p==='my-files'?'active':'' ?>" href="?p=my-files"><span class="icon">📁</span>我的文件</a>
</div></div>
<div class="body-wrap"><div class="content" id="contentArea">
<?php
$pageFile = __DIR__ . '/../admin/pages/' . $p . '.php';
if (file_exists($pageFile)) include $pageFile;
else echo '<div class="card"><div class="card-title">404</div><p>页面不存在</p></div>';
?>
</div></div>
<script>
function esc(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]})}
fetch('../version.json').then(r=>r.json()).then(v=>document.getElementById('ver').textContent=v.version).catch(()=>{});
fetch('../api/index.php?source=user_info').then(r=>r.json()).then(function(d){if(d.success)document.getElementById('userLabel').textContent=d.data.username+(d.data.role==='admin'?' (管理员)':'')}).catch(function(){});
</script>
</body></html>
