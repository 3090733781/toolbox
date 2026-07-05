<?php
@session_start();

$cfgFile = __DIR__ . '/../config.json';
if (file_exists($cfgFile)) {
    if (!file_exists(__DIR__ . '/../install/install.lock')) {
        header('Location: ../install/');
        exit;
    }
} else {
    header('Location: ../install/');
    exit;
}

$p = $_GET['p'] ?? 'dashboard';
if ($p === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ../');
    exit;
}

if (empty($_SESSION['admin']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../user/');
    exit;
}

$pages = ['dashboard', 'categories', 'plugins', 'plugins-install', 'users', 'messages', 'files', 'links', 'settings', 'admin-config', 'update'];
$pageTitle = [
    'dashboard' => '主页',
    'categories' => '分类管理',
    'plugins' => '插件管理',
    'plugins-install' => '安装新插件',
    'users' => '用户管理',
    'messages' => '留言管理',
    'files' => '文件管理',
    'links' => '友链管理',
    'settings' => '基本配置',
    'admin-config' => '管理员配置',
    'update' => '系统更新',
];
if (!in_array($p, $pages, true)) $p = 'dashboard';
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($pageTitle[$p], ENT_QUOTES, 'UTF-8') ?> - 工具箱管理后台</title>
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<link rel="stylesheet" href="css/admin.css?v=2.7">
</head>
<body>
<div class="header">
  <div class="logo">工具箱 <span>v<span id="ver"></span></span></div>
  <div class="header-right">
    <span class="user" id="userLabel"></span>
    <a href="../" target="_blank">网站首页</a>
    <a href="?p=logout">退出登录</a>
  </div>
</div>
<div class="sidebar">
  <div class="menu-group">
    <div class="menu-group-title">管理</div>
    <a class="menu-item <?= $p === 'dashboard' ? 'active' : '' ?>" href="?p=dashboard"><span class="icon">首</span><span>主页</span></a>
    <a class="menu-item <?= $p === 'categories' ? 'active' : '' ?>" href="?p=categories"><span class="icon">类</span><span>分类管理</span></a>
    <a class="menu-item" onclick="toggleSub(this)"><span class="icon">插</span><span>插件管理</span><span style="margin-left:auto;font-size:10px;color:#8a96a8">›</span></a>
    <div class="menu-sub <?= in_array($p, ['plugins', 'plugins-install'], true) ? 'open' : '' ?>">
      <a class="menu-item <?= $p === 'plugins' ? 'active' : '' ?>" href="?p=plugins"><span>插件列表</span></a>
      <a class="menu-item <?= $p === 'plugins-install' ? 'active' : '' ?>" href="?p=plugins-install"><span>安装新插件</span></a>
    </div>
    <a class="menu-item <?= $p === 'users' ? 'active' : '' ?>" href="?p=users"><span class="icon">用</span><span>用户管理</span></a>
    <a class="menu-item <?= $p === 'messages' ? 'active' : '' ?>" href="?p=messages"><span class="icon">言</span><span>留言管理</span></a>
    <a class="menu-item <?= $p === 'files' ? 'active' : '' ?>" href="?p=files"><span class="icon">文</span><span>文件管理</span></a>
    <a class="menu-item <?= $p === 'links' ? 'active' : '' ?>" href="?p=links"><span class="icon">链</span><span>友链管理</span></a>
    <a class="menu-item" onclick="toggleSub(this)"><span class="icon">设</span><span>系统配置</span><span style="margin-left:auto;font-size:10px;color:#8a96a8">›</span></a>
    <div class="menu-sub <?= in_array($p, ['settings', 'admin-config', 'update'], true) ? 'open' : '' ?>">
      <a class="menu-item <?= $p === 'settings' ? 'active' : '' ?>" href="?p=settings"><span>基本配置</span></a>
      <a class="menu-item <?= $p === 'admin-config' ? 'active' : '' ?>" href="?p=admin-config"><span>管理员配置</span></a>
      <a class="menu-item <?= $p === 'update' ? 'active' : '' ?>" href="?p=update"><span>系统更新</span></a>
    </div>
  </div>
</div>
<div class="body-wrap">
  <div class="content" id="contentArea">
<?php
$pageFile = __DIR__ . '/pages/' . $p . '.php';
if (file_exists($pageFile)) include $pageFile;
else echo '<div class="card"><div class="card-title">404</div><p>页面不存在</p></div>';
?>
  </div>
</div>
<script>
function toggleSub(el){var sub=el.nextElementSibling;if(sub&&sub.classList.contains('menu-sub')){sub.classList.toggle('open');var arr=el.querySelector('span:last-child');if(arr)arr.style.transform=sub.classList.contains('open')?'rotate(90deg)':''}}
function esc(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]})}
fetch('../version.json').then(r=>r.json()).then(v=>document.getElementById('ver').textContent=v.version).catch(()=>{});
fetch('../api/index.php?source=user_info').then(r=>r.json()).then(function(d){if(d.success)document.getElementById('userLabel').textContent=d.data.username+(d.data.role==='admin'?' (管理员)':'')}).catch(function(){});
</script>
</body>
</html>
