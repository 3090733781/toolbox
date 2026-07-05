<?php
if (!file_exists(__DIR__ . '/install/install.lock')) { header('Location: install/'); exit; }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>工具箱 - IP定位 / WHOIS查询 / 天气</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',sans-serif;
background:#f5f7fa;color:#333;min-height:100vh
}
nav{
background:#fff;border-bottom:1px solid #e8ecf1;position:sticky;top:0;z-index:50
}
.nav-inner{
max-width:900px;margin:0 auto;display:flex;align-items:center;height:60px;padding:0 20px
}
.nav-brand{font-size:20px;font-weight:700;color:#4f6af5;margin-right:32px}
.nav-links{display:flex;gap:4px}
.nav-link{
padding:8px 18px;border-radius:8px;font-size:14px;font-weight:500;
color:#666;cursor:pointer;transition:all .15s;border:none;background:none
}
.nav-link:hover{background:#f0f2ff;color:#4f6af5}
.nav-link.active{background:#4f6af5;color:#fff}
.tool-page{display:none;max-width:900px;margin:0 auto;padding:32px 20px}
.tool-page.active{display:block}

.hero{
text-align:center;padding:48px 0 36px
}
.hero h1{font-size:32px;font-weight:800;color:#1a1a2e;margin-bottom:10px}
.hero p{font-size:15px;color:#888;max-width:500px;margin:0 auto}

.search-box{
display:flex;max-width:600px;margin:0 auto;
border:2px solid #e0e4ea;border-radius:14px;overflow:hidden;
background:#fff;transition:border-color .2s;box-shadow:0 2px 8px rgba(0,0,0,0.04)
}
.search-box:focus-within{border-color:#4f6af5;box-shadow:0 4px 20px rgba(79,106,245,0.15)}
.search-box input{
flex:1;padding:16px 20px;border:none;outline:none;font-size:16px;background:transparent;color:#333
}
.search-box input::placeholder{color:#aaa}
.search-box button{
padding:16px 28px;background:#4f6af5;color:#fff;border:none;
font-size:15px;font-weight:600;cursor:pointer;transition:background .15s
}
.search-box button:hover{background:#3d56e0}

.card{
background:#fff;border-radius:16px;border:1px solid #e8ecf1;
padding:28px 32px;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,0.04)
}
.card-title{font-size:18px;font-weight:700;color:#1a1a2e;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.row{display:flex;padding:12px 0;border-bottom:1px solid #f0f2f5;align-items:flex-start}
.row:last-child{border-bottom:none}
.row-label{width:100px;flex-shrink:0;font-size:13px;color:#888;font-weight:500;padding-top:2px}
.row-value{font-size:14px;color:#333;word-break:break-all;line-height:1.5}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:24px}
.stat{
background:linear-gradient(135deg,#f0f2ff,#fff);border:1px solid #e8ecf1;
border-radius:12px;padding:18px;text-align:center
}
.stat-num{font-size:22px;font-weight:800;color:#4f6af5}
.stat-label{font-size:12px;color:#888;margin-top:4px}
.badge{
display:inline-block;background:#e8ecf1;color:#666;
padding:2px 10px;border-radius:6px;font-size:12px
}
.source-label{text-align:center;font-size:12px;color:#aaa;margin-top:12px}
.loading{text-align:center;padding:40px 0;color:#888}
.spinner{width:32px;height:32px;border:3px solid #e8ecf1;border-top-color:#4f6af5;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 12px}
@keyframes spin{to{transform:rotate(360deg)}}
.error{text-align:center;padding:24px;color:#e74c3c}
.na{color:#bbb}
.btns{display:flex;gap:6px;flex-wrap:wrap}
.btn{
padding:6px 14px;border:1px solid #e0e4ea;border-radius:8px;
background:#fff;color:#666;cursor:pointer;font-size:13px;transition:all .15s
}
.btn:hover{border-color:#4f6af5;color:#4f6af5}
.btn.active{background:#4f6af5;border-color:#4f6af5;color:#fff}
.tag{display:inline-block;background:#f0f2ff;color:#4f6af5;padding:2px 10px;border-radius:6px;font-size:12px;margin-right:6px;margin-bottom:4px}
.card-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.card-grid .card{margin-bottom:0}
.stat-days{font-size:24px;font-weight:800;color:#4f6af5;display:block}
.stat-days-label{font-size:12px;color:#888}
.card-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px}
.card-icon.blue{background:#f0f2ff}
.card-icon.green{background:#e8f5e9}
.card-icon.orange{background:#fff3e0}
.card-icon.purple{background:#f3e5f5}
.card-sm{padding:20px 24px}
.ip-input-wrap{display:flex;border:2px solid #e0e4ea;border-radius:12px;overflow:hidden;background:#fff;transition:border-color .2s;margin-bottom:14px}
.ip-input-wrap:focus-within{border-color:#4f6af5}
.ip-input-wrap input{flex:1;padding:12px 16px;border:none;outline:none;font-size:14px;background:transparent;color:#333}
.ip-input-wrap input::placeholder{color:#aaa}
.ip-input-wrap button{padding:12px 22px;background:#4f6af5;color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s}
.ip-input-wrap button:hover{background:#3d56e0}
.raw-section{margin-top:12px}
.raw-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.raw-header span{font-size:14px;font-weight:600;color:#333}
.copy-btn{
padding:6px 14px;border:1px solid #d0d5dd;border-radius:6px;background:#fff;
color:#555;font-size:12px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:4px
}
.copy-btn:hover{border-color:#4f6af5;color:#4f6af5}
.copy-btn.copied{border-color:#22c55e;color:#22c55e}
.raw-box{
  background:#f8f9fb;border:1px solid #e8ecf1;border-radius:10px;
  padding:16px;font-family:'Consolas','Courier New',monospace;font-size:12px;
  color:#555;white-space:pre-wrap;word-break:break-all;line-height:1.7;max-height:400px;overflow:auto
}
.drop-zone{
  border:2px dashed #d0d5dd;border-radius:16px;padding:48px 24px;
  text-align:center;cursor:pointer;transition:all .2s;background:#fafbfc;margin-bottom:20px
}
.drop-zone:hover,.drop-zone.dragover{border-color:#4f6af5;background:#f0f2ff}
.drop-zone-icon{font-size:48px;margin-bottom:12px;opacity:.5}
.drop-zone.dragover .drop-zone-icon{opacity:1}
.drop-zone-text{font-size:15px;color:#888;margin-bottom:8px}
.drop-zone-hint{font-size:12px;color:#aaa}
.drop-zone input{display:none}
.file-list{display:grid;gap:10px}
.file-item{
  display:flex;align-items:center;gap:12px;padding:14px 16px;
  background:#fff;border:1px solid #e8ecf1;border-radius:12px;transition:all .15s
}
.file-item:hover{border-color:#d0d5dd;box-shadow:0 1px 6px rgba(0,0,0,0.04)}
.file-icon{flex-shrink:0;width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;background:#f0f2ff;overflow:hidden}
.file-icon img{width:100%;height:100%;object-fit:cover;border-radius:8px}
.file-icon .ext-tag{font-size:9px;font-weight:700;color:#4f6af5;text-transform:uppercase}
.file-info{flex:1;min-width:0}
.file-name{font-size:14px;font-weight:600;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.file-meta{font-size:12px;color:#999;margin-top:2px}
.file-actions{display:flex;gap:4px;flex-shrink:0}
.file-btn{
  padding:6px 10px;border:1px solid #e0e4ea;border-radius:6px;background:#fff;
  color:#666;font-size:12px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:3px
}
.file-btn:hover{border-color:#4f6af5;color:#4f6af5}
.file-btn.danger:hover{border-color:#e74c3c;color:#e74c3c}
.file-btn.copied{border-color:#22c55e;color:#22c55e}
.file-empty{text-align:center;padding:40px 0;color:#aaa;font-size:14px}
.upload-progress{text-align:center;padding:20px;color:#888}
@media(max-width:768px){
  .nav-inner{flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch;gap:2px;scrollbar-width:none;padding:0 12px}
  .nav-inner::-webkit-scrollbar{display:none}
  .nav-brand{font-size:15px;margin-right:10px;flex-shrink:0}
  .nav-link{font-size:12px;padding:8px 10px;white-space:nowrap}
  .tool-page{padding:20px 12px}
  .hero{padding:28px 0 24px}
  .hero h1{font-size:24px}
  .hero p{font-size:14px}
  .card{padding:20px 16px;border-radius:12px}
  .card-sm{padding:16px}
  .card-grid{grid-template-columns:1fr;gap:12px}
  .stats{grid-template-columns:repeat(2,1fr);gap:8px}
  .stat{padding:14px 10px}
  .stat-num{font-size:18px}
  .row{padding:10px 0}
  .row-label{width:80px;font-size:12px}
  .row-value{font-size:13px}
  .search-box input,.ip-input-wrap input{min-height:44px;font-size:15px;padding:12px 14px}
  .search-box button,.ip-input-wrap button{min-height:44px;font-size:14px;padding:12px 18px}
  .btn{padding:8px 11px;font-size:12px;min-height:36px}
  .btns{gap:4px}
  .source-label{font-size:11px}
  .raw-box{font-size:11px;padding:12px}
  #weatherResult .card div[style*="grid-template-columns:1fr 1fr 1fr"]{grid-template-columns:1fr!important;gap:8px}
  #weatherResult .card div[style*="font-size:56px"]{font-size:38px!important}
}
.nav-right{display:flex;align-items:center;gap:8px;margin-left:auto;flex-shrink:0}
.user-btn{padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid #e0e4ea;background:#fff;color:#666;transition:all .15s}
.user-btn:hover{border-color:#4f6af5;color:#4f6af5}
.user-btn.primary{background:#4f6af5;color:#fff;border-color:#4f6af5}
.user-btn.primary:hover{background:#3d56e0}
.user-info{font-size:12px;color:#4f6af5;font-weight:600;display:flex;align-items:center;gap:6px}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center}
.modal-overlay.show{display:flex}
.modal{background:#fff;border-radius:16px;padding:32px;width:360px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.modal h2{font-size:18px;font-weight:700;margin-bottom:4px;color:#1a1a2e}
.modal p{font-size:13px;color:#888;margin-bottom:20px}
.modal .form-group{margin-bottom:14px}
.modal .form-group label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:4px}
.modal .form-group input{width:100%;padding:10px 14px;border:2px solid #e0e4ea;border-radius:8px;font-size:14px;outline:none}
.modal .form-group input:focus{border-color:#4f6af5}
.modal .form-row{display:flex;gap:8px;margin-top:4px;font-size:13px;color:#888}
.modal .form-row a{color:#4f6af5;text-decoration:none;font-weight:600;cursor:pointer}
.modal .form-row a:hover{text-decoration:underline}
.modal .modal-err{color:#e74c3c;font-size:12px;margin-top:8px;display:none}

/* 2026 UI refresh: frontend polish layer */
:root{--ui-bg:#f1f5f8;--ui-card:#fff;--ui-text:#182233;--ui-muted:#667386;--ui-line:#dbe4ee;--ui-primary:#137cbd;--ui-primary-dark:#0f6ca6;--ui-accent:#13a37f;--ui-warn:#d98b22;--ui-danger:#d84a3a;--ui-shadow:0 18px 44px rgba(31,45,61,.08);--ui-radius:8px}
body{background:linear-gradient(180deg,#f8fbfd 0,#f1f5f8 48%,#eaf0f6 100%);color:var(--ui-text);font-size:14px;line-height:1.55}
body::before{content:"";position:fixed;inset:0;z-index:-1;background:radial-gradient(circle at 12% 0,rgba(19,124,189,.08),transparent 34%),radial-gradient(circle at 92% 6%,rgba(19,163,127,.08),transparent 30%)}
nav{background:rgba(255,255,255,.9);border-bottom:1px solid rgba(126,143,164,.22);box-shadow:0 12px 30px rgba(31,45,61,.06);backdrop-filter:blur(14px)}
.nav-inner{max-width:1120px;height:64px}
.nav-brand{display:flex;align-items:center;gap:8px;margin-right:28px;color:var(--ui-text);font-weight:850;letter-spacing:0}
.nav-brand::before{content:"";width:10px;height:10px;border-radius:99px;background:var(--ui-accent);box-shadow:0 0 0 5px rgba(19,163,127,.12)}
.nav-link{min-height:36px;padding:8px 14px;border:1px solid transparent;border-radius:7px;color:#536173;font-weight:750}
.nav-link:hover{background:#f2f7fb;border-color:#dde8f1;color:var(--ui-primary)}
.nav-link.active{background:#e8f5ff;border-color:#cbe7fb;color:#0f6ca6;box-shadow:0 8px 20px rgba(19,124,189,.08)}
.user-btn{min-height:34px;border-radius:7px;border-color:#ccd7e3;color:#536173;font-weight:750}
.user-btn:hover{background:#f2f7fb;border-color:var(--ui-primary);color:var(--ui-primary)}
.user-btn.primary{background:var(--ui-primary);border-color:var(--ui-primary);box-shadow:0 8px 18px rgba(19,124,189,.18)}
.user-btn.primary:hover{background:var(--ui-primary-dark)}
.tool-page{max-width:1120px;padding:34px 20px}
.hero{padding:42px 0 34px;text-align:left}
.hero h1{font-size:34px;line-height:1.15;color:var(--ui-text);letter-spacing:0}
.hero p{max-width:680px;margin:10px 0 0;color:var(--ui-muted);font-size:15px}
.search-box,.ip-input-wrap{max-width:760px;margin-left:0;border:1px solid var(--ui-line);border-radius:8px;background:rgba(255,255,255,.98);box-shadow:var(--ui-shadow)}
.search-box:focus-within,.ip-input-wrap:focus-within{border-color:var(--ui-primary);box-shadow:0 0 0 3px rgba(19,124,189,.13),var(--ui-shadow)}
.search-box input,.ip-input-wrap input{color:var(--ui-text)}
.search-box button,.ip-input-wrap button{background:var(--ui-primary);font-weight:800}
.search-box button:hover,.ip-input-wrap button:hover{background:var(--ui-primary-dark)}
.card{border:1px solid rgba(126,143,164,.24);border-radius:8px;background:rgba(255,255,255,.96);box-shadow:var(--ui-shadow);padding:24px}
.card-title{padding-bottom:12px;border-bottom:1px solid #edf2f6;color:var(--ui-text);font-size:16px;font-weight:850}
.card-sm{padding:20px}
.row{border-bottom-color:#edf2f6}
.row-label{color:#718094;font-weight:750}
.row-value{color:var(--ui-text)}
.stats{gap:14px}
.stat{position:relative;overflow:hidden;min-height:96px;border:1px solid rgba(126,143,164,.22);border-radius:8px;background:#fff;text-align:left;box-shadow:0 14px 34px rgba(31,45,61,.06)}
.stat::after{content:"";position:absolute;right:16px;top:16px;width:34px;height:34px;border-radius:999px;background:#e8f5ff;border:1px solid #cbe7fb}
.stat:nth-child(2)::after{background:#edf9f3;border-color:#c6ecd8}
.stat:nth-child(3)::after{background:#fff7e8;border-color:#f6d8a6}
.stat:nth-child(4)::after{background:#f4efff;border-color:#ddd2ff}
.stat-num{position:relative;z-index:1;color:var(--ui-text)}
.stat-label{position:relative;z-index:1;color:var(--ui-muted);font-weight:750}
.badge,.tag{border-radius:999px;background:#edf4fa;color:#536173;font-weight:750}
.btn,.file-btn,.copy-btn{border-radius:7px;border-color:#cfd9e5;background:#fff;color:#536173;font-weight:750}
.btn:hover,.file-btn:hover,.copy-btn:hover{background:#f0fbf7;border-color:var(--ui-accent);color:#0f705d}
.btn.active{background:var(--ui-primary);border-color:var(--ui-primary);color:#fff}
.drop-zone{border-color:#cbd7e4;border-radius:8px;background:#fbfdff}
.drop-zone:hover,.drop-zone.dragover{border-color:var(--ui-primary);background:#eef8ff}
.file-item{border-color:#dfe7f0;border-radius:8px;box-shadow:0 8px 20px rgba(31,45,61,.04)}
.file-item:hover{transform:translateY(-1px);border-color:#c7d6e5;box-shadow:0 14px 28px rgba(31,45,61,.07)}
.raw-box{border-color:#dfe7f0;border-radius:8px;background:#f8fbfd;color:#435064}
.modal-overlay{background:rgba(15,23,42,.48);backdrop-filter:blur(6px)}
.modal{border:1px solid rgba(126,143,164,.24);border-radius:8px;box-shadow:0 24px 70px rgba(15,23,42,.22)}
.modal h2{color:var(--ui-text)}
.modal p{color:var(--ui-muted)}
.modal .form-group input{border:1px solid #ccd7e3;border-radius:7px}
.modal .form-group input:focus{border-color:var(--ui-primary);box-shadow:0 0 0 3px rgba(19,124,189,.13)}
.loading{color:var(--ui-muted)}
.spinner{border-color:#dce6ef;border-top-color:var(--ui-accent)}
.error{color:var(--ui-danger)}
@media(max-width:768px){
  .nav-inner{height:58px}
  .nav-brand{font-size:14px;margin-right:8px}
  .nav-link{min-height:34px;border-radius:6px}
  .nav-right{gap:6px}
  .tool-page{padding:20px 12px}
  .hero{text-align:left;padding:26px 0 22px}
  .hero h1{font-size:25px}
  .search-box,.ip-input-wrap{max-width:none}
  .card{padding:18px 15px}
  .stats{grid-template-columns:1fr 1fr}
  .file-actions{flex-wrap:wrap;justify-content:flex-end}
}
@media(max-width:520px){
  .search-box,.ip-input-wrap{display:block;overflow:visible}
  .search-box input,.ip-input-wrap input{width:100%}
  .search-box button,.ip-input-wrap button{width:100%;border-radius:0 0 8px 8px}
  .stats{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div class="modal-overlay" id="loginModal">
<div class="modal">
<h2 id="modalTitle">🔐 登录</h2>
<p id="modalSub">登录后可使用文件中转站</p>
<div id="modalLogin">
<div class="form-group"><label>用户名</label><input type="text" id="loginUser" placeholder="输入用户名" value="admin" onkeydown="if(event.key==='Enter')document.getElementById('loginPwd').focus()"></div>
<div class="form-group"><label>密码</label><input type="password" id="loginPwd" placeholder="输入密码" onkeydown="if(event.key==='Enter')doModalLogin()"></div>
<button class="btn btn-primary" onclick="doModalLogin()" style="width:100%;padding:12px;font-size:14px">登录</button>
<div style="margin-top:16px;padding-top:16px;border-top:1px solid #e8ecf1">
<div style="font-size:12px;color:#999;margin-bottom:10px;text-align:center">第三方登录</div>
<div style="display:flex;gap:10px;justify-content:center">
<a href="api/oauth/connect.php?type=qq" style="display:flex;align-items:center;gap:4px;padding:8px 20px;border:1px solid #e0e4ea;border-radius:8px;text-decoration:none;font-size:13px;color:#333;transition:all .15s" onmouseover="this.style.borderColor='#12b7f5'" onmouseout="this.style.borderColor='#e0e4ea'">💬 QQ登录</a>
</div>
</div>
</div>
<div id="modalErr" class="modal-err"></div>
</div>
</div>

<nav>
<div class="nav-inner">
<div class="nav-brand">🛠 工具箱</div>
<div class="nav-links">
<button class="nav-link active" data-page="ip-tool">📍 IP定位</button>
<button class="nav-link" data-page="whois-tool">🔍 WHOIS查询</button>
<button class="nav-link" data-page="weather-tool">🌤 天气</button>
<button class="nav-link" data-page="icp-tool">📜 ICP备案</button>
<button class="nav-link" data-page="file-tool">📁 文件中转</button>
<button class="nav-link" data-page="msg-tool">💬 留言板</button>
<button class="nav-link" data-page="plugin-market">🏪 插件列表</button>
</div>
<div class="nav-links" id="categoryNav" style="display:none"></div>
<div class="nav-right">
<span class="user-info" id="userInfo" style="display:none"></span>
<button class="user-btn" id="loginBtn" onclick="openModal()">登录</button>
<span id="userMenu" style="display:none"><button class="user-btn" onclick="doLogout()">退出</button></span>
</div>
</div>
</nav>

<div id="ip-tool" class="tool-page active">
<div class="stats">
<div class="stat"><div class="stat-num">7</div><div class="stat-label">API 数据源</div></div>
<div class="stat"><div class="stat-num">实时</div><div class="stat-label">IP 定位</div></div>
<div class="stat"><div class="stat-num">自动</div><div class="stat-label">天气信息</div></div>
</div>

<div class="card">
<div class="card-title">📍 IP 定位</div>
<div class="ip-input-wrap">
<input type="text" id="ipInput" placeholder="输入 IP 或域名（留空查本机）" spellcheck="false">
<button onclick="fetchCustomIP()">查询</button>
</div>
<div class="btns" id="ipSourceBtns" style="margin-bottom:16px">
<button class="btn active" data-api="ip-api">ip-api.com</button>
<button class="btn" data-api="ip-sb">ip.sb</button>
<button class="btn" data-api="ipwhois">ipwho.is</button>
<button class="btn" data-api="ipip">IPIP.net</button>
<button class="btn" data-api="ip-baidu">百度</button>
<button class="btn" data-api="ip-baota">宝塔</button>
<button class="btn" data-api="ip9">ip9.com.cn</button>
</div>
<div id="ipContent"><div class="loading"><div class="spinner"></div>正在获取位置信息...</div></div>
<div id="ipSourceInfo" class="source-label"></div>
</div>

<div class="card" id="ipWeatherCard" style="display:none">
<div class="card-title">🌤 当前天气</div>
<div id="ipWeatherContent"></div>
</div>
</div>

<div id="whois-tool" class="tool-page">
<div class="hero">
<h1>🔍 WHOIS 查询</h1>
<p>查询域名注册信息、持有者、注册商、到期时间等</p>
</div>

<div class="search-box">
<input type="text" id="domainInput" placeholder="输入域名，如：example.com" spellcheck="false" autofocus>
<button onclick="doWhois()">查询</button>
</div>

<div id="whoisResult" style="margin-top:28px"></div>

<div class="stats" style="margin-top:20px">
<div class="stat"><div class="stat-num">.com/.net</div><div class="stat-label">RDAP 查询</div></div>
<div class="stat"><div class="stat-num">.org/.cn</div><div class="stat-label">WHOIS 直查</div></div>
<div class="stat"><div class="stat-num">多服务器</div><div class="stat-label">自动回退</div></div>
</div>
</div>

<div id="icp-tool" class="tool-page">
<div class="hero">
<h1>📜 ICP 备案查询</h1>
<p>支持输入域名、程序名称、网站备案号、主体备案号、单位名称进行查询</p>
</div>
<div class="search-box">
<input type="text" id="icpInput" placeholder="输入域名/备案号/单位名称，如：qq.com" spellcheck="false">
<button onclick="doIcp()">查询</button>
</div>
<div id="icpResult" style="margin-top:28px"></div>
<div class="stats" style="margin-top:20px">
<div class="stat"><div class="stat-num">备案号</div><div class="stat-label">ICP 许可证</div></div>
<div class="stat"><div class="stat-num">主办单位</div><div class="stat-label">企业/个人</div></div>
<div class="stat"><div class="stat-num">审核日期</div><div class="stat-label">最新备案</div></div>
</div>
<div class="card" style="font-size:13px;color:#666;line-height:1.7;padding:14px 20px">
此 ICP 查询工具直接对接工信部官网，非第三方接口。采用开源项目 <a href="https://github.com/HG-ha/ICP_Query" target="_blank" style="color:#4f6af5">ICP_Query</a>。
</div>
</div>

<div id="weather-tool" class="tool-page">
<div class="hero">
<h1>🌤 天气查询</h1>
<p>查询实时天气信息（数据来源：高德地图）</p>
</div>

<div class="search-box">
<input type="text" id="cityInput" placeholder="输入城市名，如：北京、上海、深圳" spellcheck="false" autofocus>
<button onclick="doWeather()">查询</button>
</div>

<div id="weatherResult" style="margin-top:28px"></div>

<div class="stats" style="margin-top:20px">
<div class="stat"><div class="stat-num">实时</div><div class="stat-label">天气实况</div></div>
<div class="stat"><div class="stat-num">高德</div><div class="stat-label">数据来源</div></div>
<div class="stat"><div class="stat-num">自动</div><div class="stat-label">IP 定位城市</div></div>
</div>
</div>

<div id="file-tool" class="tool-page">
<div class="hero">
<h1>📁 文件中转站</h1>
<p>拖拽或点击上传文件，方便 AI 读取和分享</p>
</div>

<div id="uploadArea">
<div class="drop-zone" id="dropZone">
<div class="drop-zone-icon">📤</div>
<div class="drop-zone-text">拖拽文件到此处，或点击选择文件</div>
<div class="drop-zone-hint">支持图片、文档、代码、压缩包等，单文件最大 50MB</div>
<input type="file" id="fileInput" multiple>
</div>
<div id="uploadProgress" style="display:none"><div class="upload-progress"><div class="spinner"></div>上传中...</div></div>
</div>
<div id="loginPrompt" style="display:none;text-align:center;padding:40px 20px;background:#fff;border:1px solid #e8ecf1;border-radius:16px;margin-bottom:20px">
<div style="font-size:40px;margin-bottom:12px">🔐</div>
<div style="font-size:15px;color:#666;margin-bottom:16px">请先登录后上传文件</div>
<button class="user-btn primary" onclick="openModal()">登录 / 注册</button>
</div>

<div class="card">
<div class="card-title" style="justify-content:space-between;flex-wrap:wrap">
<span>📋 文件列表</span>
<button class="file-btn" onclick="loadFileList()" style="font-size:12px">🔄 刷新</button>
</div>
<div id="fileList"><div class="file-empty">暂无文件</div></div>
</div>

<div class="card" id="apiKeyCard" style="display:none">
<div class="card-title" style="justify-content:space-between;flex-wrap:wrap">
<span>🔑 API 密钥</span>
<button class="file-btn" onclick="regenerateApiKey()">🔄 重新生成</button>
</div>
<p style="font-size:13px;color:#888;margin-bottom:12px">用于 AI 文件传输，支持 GET/POST 请求</p>
<div style="display:flex;gap:8px">
<input type="text" id="apiKeyDisplay" readonly style="flex:1;padding:10px 14px;border:2px solid #e0e4ea;border-radius:8px;font-size:13px;font-family:monospace;background:#f8f9fb;color:#333;outline:none">
<button class="file-btn" onclick="copyApiKey()" id="copyApiKeyBtn">📋 复制</button>
</div>
<div style="margin-top:12px;font-size:12px;color:#999;line-height:1.6">
<strong>使用方式：</strong><br>
上传：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">curl -X POST http://cx.23zo.cn/api/index.php?source=file_upload&key=你的密钥 -F "file=@图片.jpg"</code><br>
下载：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">curl -O "http://cx.23zo.cn/api/index.php?source=file_download&file=图片.jpg"</code>
</div>
</div>

<div class="stats" style="margin-top:20px">
<div class="stat"><div class="stat-num" id="fileCount">0</div><div class="stat-label">文件总数</div></div>
<div class="stat"><div class="stat-num" id="fileTotalSize">0 B</div><div class="stat-label">总大小</div></div>
<div class="stat"><div class="stat-num">拖拽</div><div class="stat-label">快速上传</div></div>
</div>
</div>

<div id="msg-tool" class="tool-page">
<?php include __DIR__ . '/modules/messages.php'; ?>
</div>

<div id="plugin-market" class="tool-page">
<?php include __DIR__ . '/plugins/market/page.php'; ?>
</div>

<script>
function switchTab(id) {
document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
document.querySelectorAll('.tool-page').forEach(p => p.classList.remove('active'));
var link=document.querySelector('[data-page="'+id+'"]');if(link)link.classList.add('active');
var page=document.getElementById(id);if(page)page.classList.add('active');
if(id==='plugin-market'&&typeof loadPluginList==='function')loadPluginList();
}
document.querySelectorAll('.nav-link').forEach(function(link){link.addEventListener('click',function(){var id=this.dataset.page;switchTab(id);history.pushState(null,'','?p='+id)})});
window.addEventListener('popstate',function(){var p=new URLSearchParams(location.search).get('p');if(p)switchTab(p)});
(function(){var p=new URLSearchParams(location.search).get('p');if(p&&document.getElementById(p))switchTab(p)})();

document.getElementById('domainInput').addEventListener('keydown', e => { if (e.key === 'Enter') doWhois(); });
document.getElementById('ipInput').addEventListener('keydown', e => { if (e.key === 'Enter') fetchCustomIP(); });
document.getElementById('cityInput').addEventListener('keydown', e => { if (e.key === 'Enter') doWeather(); });
document.getElementById('icpInput').addEventListener('keydown', e => { if (e.key === 'Enter') doIcp(); });

function formatSize(b) {
  if (b === 0) return '0 B';
  const u = ['B','KB','MB','GB']; let i = 0;
  while (b >= 1024 && i < u.length - 1) { b /= 1024; i++; }
  return (i > 0 ? b.toFixed(1) : b) + ' ' + u[i];
}

function isImage(n) { return /\.(jpg|jpeg|png|gif|webp|svg|bmp|ico)$/i.test(n); }

function fileIcon(n) {
  if (isImage(n)) return '';
  if (/\.(mp4|webm|avi|mov|mkv)$/i.test(n)) return '🎬';
  if (/\.(mp3|wav|ogg|flac|aac)$/i.test(n)) return '🎵';
  if (/\.(pdf)$/i.test(n)) return '📄';
  if (/\.(doc|docx)$/i.test(n)) return '📝';
  if (/\.(xls|xlsx)$/i.test(n)) return '📊';
  if (/\.(zip|rar|7z|gz|tar)$/i.test(n)) return '📦';
  if (/\.(js|ts|py|java|c|cpp|h|php|html|css|json|xml|yaml|yml|md|sql|rb|go|rs)$/i.test(n)) return '💻';
  if (/\.(txt|csv|log)$/i.test(n)) return '📃';
  return '📎';
}

function extTag(n) { const e = n.split('.').pop(); return e ? e.toUpperCase() : ''; }

function loadFileList() {
  const el = document.getElementById('fileList');
  el.innerHTML = '<div class="upload-progress"><div class="spinner"></div>加载中...</div>';
  fetch('api/index.php?source=file_list').then(r => r.json()).then(d => {
    if (!d.success || !d.data.length) {
      el.innerHTML = '<div class="file-empty">暂无文件</div>';
      document.getElementById('fileCount').textContent = '0';
      document.getElementById('fileTotalSize').textContent = '0 B';
      return;
    }
    let totalSize = 0;
    const items = d.data.map(f => {
      totalSize += f.size;
      const ico = isImage(f.name)
        ? `<img src="api/index.php?source=file_download&file=${encodeURIComponent(f.name)}" alt="">`
        : `<span class="ext-tag">${extTag(f.name)}</span>`;
      return `<div class="file-item">
        <div class="file-icon">${ico || fileIcon(f.name)}</div>
        <div class="file-info">
          <div class="file-name" title="${esc(f.name)}">${esc(f.name)}</div>
          <div class="file-meta">${formatSize(f.size)} · ${new Date(f.time * 1000).toLocaleString('zh-CN')}</div>
        </div>
        <div class="file-actions">
          <a class="file-btn" href="api/index.php?source=file_download&file=${encodeURIComponent(f.name)}" download="${esc(f.name)}">⬇ 下载</a>
          <button class="file-btn" onclick="copyLink('${encodeURIComponent(f.name)}',this)">🔗 外链</button>
          ${(g_user && (g_user.role === 'admin' || g_user.id == f.user_id)) ? `<button class="file-btn danger" onclick="deleteFile('${esc(f.name)}')">🗑 删除</button>` : ''}
        </div>
      </div>`;
    });
    el.innerHTML = items.join('');
    document.getElementById('fileCount').textContent = d.data.length;
    document.getElementById('fileTotalSize').textContent = formatSize(totalSize);
  }).catch(() => {
    el.innerHTML = '<div class="error">加载失败</div>';
  });
}

function copyFileName(n, btn) {
  navigator.clipboard.writeText(n).then(() => {
    btn.classList.add('copied'); btn.textContent = '✓ 已复制';
    setTimeout(() => { btn.classList.remove('copied'); btn.textContent = '📋 复制'; }, 2000);
  }).catch(() => {});
}

function copyLink(name, btn) {
  const url = location.origin + '/api/index.php?source=file_download&file=' + name;
  copyText(url, btn, '🔗 外链');
}

function copyText(text, btn, label) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(() => done()).catch(() => fallback());
  } else { fallback(); }
  function done() { btn.classList.add('copied'); btn.textContent = '✓ 已复制'; setTimeout(() => { btn.classList.remove('copied'); btn.textContent = label; }, 2000); }
  function fallback() {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.left = '-9999px';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); done(); } catch(e) { prompt('手动复制：', text); }
    document.body.removeChild(ta);
  }
}

function deleteFile(n) {
  if (!confirm(`确定删除「${n}」？`)) return;
  fetch('api/index.php?source=file_delete&file=' + encodeURIComponent(n))
    .then(r => r.json()).then(d => {
      if (d.success) loadFileList();
      else alert('删除失败：' + d.error);
    }).catch(() => alert('删除失败'));
}

function uploadFiles(files) {
  if (!files || !files.length) return;
  const progress = document.getElementById('uploadProgress');
  progress.style.display = '';
  const form = new FormData();
  for (const f of files) form.append('file[]', f);
  fetch('api/index.php?source=file_upload_array', { method: 'POST', body: form })
    .then(r => r.json()).then(d => {
      progress.style.display = 'none';
      if (d.success) loadFileList();
      else alert('上传失败：' + (d.error || '未知错误'));
    }).catch(() => {
      progress.style.display = 'none';
      alert('上传失败：网络错误');
    });
}

const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('dragover');
  uploadFiles(e.dataTransfer.files);
});
fileInput.addEventListener('change', () => {
  uploadFiles(fileInput.files);
  fileInput.value = '';
});

let g_user = null;

function openModal() { document.getElementById('loginModal').classList.add('show'); document.getElementById('loginUser').focus(); }
function closeModal() { document.getElementById('loginModal').classList.remove('show'); document.getElementById('modalErr').style.display='none'; }
document.getElementById('loginModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

function doModalLogin() {
  const user = document.getElementById('loginUser').value.trim();
  const pwd = document.getElementById('loginPwd').value;
  if (!user || !pwd) { document.getElementById('modalErr').textContent = '请输入用户名和密码'; document.getElementById('modalErr').style.display = ''; return; }
  fetch('api/index.php?source=user_login', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({username: user, password: pwd})
  }).then(r => r.json()).then(d => {
    if (d.success) { document.getElementById('modalErr').style.display = 'none'; closeModal(); checkUser(); }
    else { document.getElementById('modalErr').textContent = d.error || '登录失败'; document.getElementById('modalErr').style.display = ''; }
  }).catch(() => { document.getElementById('modalErr').textContent = '网络错误'; document.getElementById('modalErr').style.display = ''; });
}

function doLogout() {
  fetch('api/index.php?source=user_logout').then(() => { g_user = null; checkUser(); });
}

function checkUser() {
  fetch('api/index.php?source=user_info').then(r => r.json()).then(d => {
    const loggedIn = d.success;
    g_user = d.data || null;
    if (loggedIn) {
      const label = d.data.username + (d.data.role === 'admin' ? ' (管理员)' : '');
      const info = document.getElementById('userInfo');
      info.textContent = label;
      info.style.cursor = 'pointer';
      info.title = '点击进入' + (d.data.role === 'admin' ? '管理后台' : '用户中心');
      info.onclick = () => { window.location.href = d.data.role === 'admin' ? 'admin/' : 'user/'; };
      info.style.display = '';
      document.getElementById('loginBtn').style.display = 'none';
      document.getElementById('userMenu').style.display = '';
    } else {
      document.getElementById('userInfo').style.display = 'none';
      document.getElementById('loginBtn').style.display = '';
      document.getElementById('userMenu').style.display = 'none';
    }
    const uploadArea = document.getElementById('uploadArea');
    const loginPrompt = document.getElementById('loginPrompt');
    if (uploadArea) uploadArea.style.display = loggedIn ? '' : 'none';
    if (loginPrompt) loginPrompt.style.display = loggedIn ? 'none' : '';
    loadFileList();
    if (loggedIn) loadApiKey();
  }).catch(() => {});
}

function loadApiKey() {
  const card = document.getElementById('apiKeyCard');
  if (!card) return;
  card.style.display = '';
  fetch('api/index.php?source=user_api_key').then(r => r.json()).then(d => {
    if (d.success && d.data.api_key) {
      document.getElementById('apiKeyDisplay').value = d.data.api_key;
    }
  }).catch(() => {});
}

function regenerateApiKey() {
  if (!confirm('重新生成后旧密钥将失效，确定？')) return;
  fetch('api/index.php?source=user_api_key', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({regenerate: true})
  }).then(r => r.json()).then(d => {
    if (d.success) document.getElementById('apiKeyDisplay').value = d.data.api_key;
    else alert(d.error || '生成失败');
  }).catch(() => { alert('网络错误'); });
}

function copyApiKey() {
  copyText(document.getElementById('apiKeyDisplay').value, document.getElementById('copyApiKeyBtn'), '📋 复制');
}

const LABELS = {
'ip-api': { name: 'ip-api.com', note: '功能完整，推荐' },
'ip-sb': { name: 'ip.sb', note: '功能完整' },
'ipwhois': { name: 'ipwho.is', note: '功能完整' },
    'ipip': { name: 'IPIP.net', note: '无经纬度' },
    'ip-baidu': { name: '百度', note: '国内定位' },
    'ip-baota': { name: '宝塔', note: '含经纬度' },
    'ip9': { name: 'ip9.com.cn', note: '国内，含经纬度' },
};

function esc(s) { return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]); }

function renderIP(data) {
const el = document.getElementById('ipContent');
if (!data.success) { el.innerHTML = `<div class="error">${esc(data.error)}</div>`; return; }
const d = data.data;
const noLatLon = !d.lat && !d.lon;
el.innerHTML = `
<div class="row"><div class="row-label">IP 地址</div><div class="row-value">${esc(d.ip)}</div></div>
<div class="row"><div class="row-label">国家</div><div class="row-value">${esc(d.country)}</div></div>
<div class="row"><div class="row-label">地区</div><div class="row-value">${esc(d.region)}</div></div>
<div class="row"><div class="row-label">经纬度</div><div class="row-value">${noLatLon ? '<span class="na">N/A</span>' : '<span class="tag">'+esc(d.lat+' , '+d.lon)+'</span>'}</div></div>
<div class="row"><div class="row-label">ISP</div><div class="row-value">${esc(d.isp) || '<span class="na">N/A</span>'}</div></div>
<div class="row"><div class="row-label">组织</div><div class="row-value">${esc(d.org) || '<span class="na">N/A</span>'}</div></div>
<div class="row"><div class="row-label">时区</div><div class="row-value">${esc(d.timezone) || '<span class="na">N/A</span>'}</div></div>`;
}

function renderIPWeather(d) {
const card = document.getElementById('ipWeatherCard');
const el = document.getElementById('ipWeatherContent');
if (!d.success) { card.style.display = 'none'; return; }
card.style.display = '';
const w = d.data;
el.innerHTML = `
<div class="row"><div class="row-label">城市</div><div class="row-value">${esc(w.city)} ${esc(w.province)}</div></div>
<div class="row"><div class="row-label">天气</div><div class="row-value">${esc(w.condition)}</div></div>
<div class="row"><div class="row-label">温度</div><div class="row-value">${esc(w.temp)}</div></div>
<div class="row"><div class="row-label">湿度</div><div class="row-value">${esc(w.humidity)}</div></div>
<div class="row"><div class="row-label">风向</div><div class="row-value">${esc(w.wind_dir)} ${esc(w.wind_power)}</div></div>
<div class="row"><div class="row-label">更新时间</div><div class="row-value">${esc(w.report_time)}</div></div>`;
}

function renderWeatherPage(d) {
const el = document.getElementById('weatherResult');
if (!d.success) { el.innerHTML = `<div class="error">${esc(d.error)}</div>`; return; }
const w = d.data;
el.innerHTML = `
<div class="card">
<div class="card-title" style="font-size:20px;justify-content:center">${esc(w.city)} · ${esc(w.province)} <span class="tag" style="font-size:14px;margin-left:8px">${esc(w.condition)}</span></div>
<div style="text-align:center;padding:12px 0 8px"><span style="font-size:56px;font-weight:800;color:#1a1a2e">${esc(w.temp)}</span></div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:16px">
<div style="text-align:center;padding:12px;background:#f8f9fb;border-radius:10px"><div style="font-size:13px;color:#888">湿度</div><div style="font-size:18px;font-weight:600;color:#333;margin-top:4px">${esc(w.humidity)}</div></div>
<div style="text-align:center;padding:12px;background:#f8f9fb;border-radius:10px"><div style="font-size:13px;color:#888">风向</div><div style="font-size:18px;font-weight:600;color:#333;margin-top:4px">${esc(w.wind_dir)} ${esc(w.wind_power)}</div></div>
<div style="text-align:center;padding:12px;background:#f8f9fb;border-radius:10px"><div style="font-size:13px;color:#888">更新时间</div><div style="font-size:18px;font-weight:600;color:#333;margin-top:4px">${esc(w.report_time)}</div></div>
</div>
</div>`;
}

function fetchWeather(city) {
return fetch('api/index.php?source=weather_amap&city=' + encodeURIComponent(city)).then(r => r.json());
}

function renderIcp(d) {
const el = document.getElementById('icpResult');
if (!d.success) { el.innerHTML = `<div class="error">${esc(d.error)}</div>`; return; }
const r = d.data;
el.innerHTML = `
<div class="card"><div class="card-title">📜 ICP 备案信息</div>
<div class="row"><div class="row-label">域名</div><div class="row-value">${esc(r.domain)}</div></div>
<div class="row"><div class="row-label">备案号</div><div class="row-value"><span class="tag" style="font-size:14px">${esc(r.icp)}</span></div></div>
<div class="row"><div class="row-label">主办单位</div><div class="row-value">${esc(r.unit)}</div></div>
<div class="row"><div class="row-label">单位性质</div><div class="row-value">${esc(r.nature) || '<span class="na">N/A</span>'}</div></div>
<div class="row"><div class="row-label" style="border-bottom:none">审核日期</div><div class="row-value" style="border-bottom:none">${esc(r.audit_date)}</div></div>
</div>`;
}

function doIcp() {
const domain = document.getElementById('icpInput').value.trim();
if (!domain) return;
const el = document.getElementById('icpResult');
el.innerHTML = '<div class="loading"><div class="spinner"></div>查询中...</div>';
fetch('api/index.php?source=icp&domain=' + encodeURIComponent(domain))
.then(r => r.json()).then(d => renderIcp(d))
.catch(e => { el.innerHTML = `<div class="error">查询失败：${esc(e.message)}</div>`; });
}

function doWeather() {
const city = document.getElementById('cityInput').value.trim();
if (!city) return;
const el = document.getElementById('weatherResult');
el.innerHTML = '<div class="loading"><div class="spinner"></div>查询中...</div>';
fetchWeather(city).then(d => renderWeatherPage(d)).catch(() => { el.innerHTML = '<div class="error">查询失败</div>'; });
}

function fetchCustomIP() {
let val = document.getElementById('ipInput').value.trim();
if (!val) val = g_clientIP;
const activeBtn = document.querySelector('.btn.active');
const source = activeBtn ? activeBtn.dataset.api : 'ip-api';
const el = document.getElementById('ipContent');
el.innerHTML = '<div class="loading"><div class="spinner"></div>查询中...</div>';
const info = LABELS[source] || {};
document.getElementById('ipSourceInfo').textContent = '数据来源：' + (info.name || source) + ' | 查询：' + val;
const ctrl = new AbortController();
const t = setTimeout(() => ctrl.abort(), 15000);
fetch('api/index.php?source=' + encodeURIComponent(source) + '&query=' + encodeURIComponent(val), { signal: ctrl.signal })
.then(r => r.json()).then(d => { clearTimeout(t); renderIP(d); })
.catch(e => { clearTimeout(t); el.innerHTML = `<div class="error">请求失败：${esc(e.message)}</div>`; });
}

function fetchAPI(source) {
queryIP(source, g_clientIP || '未知');
}

function daysBetween(d1, d2) {
const a = new Date(d1), b = new Date(d2);
return Math.floor((b - a) / 86400000);
}

function formatDate(s) {
if (!s) return '';
const d = new Date(s);
const y = d.getFullYear();
const m = String(d.getMonth()+1).padStart(2,'0');
const day = String(d.getDate()).padStart(2,'0');
return y+'/'+m+'/'+day;
}

let g_rawWhois = '';

function copyRaw() {
const btn = document.getElementById('copyRawBtn');
navigator.clipboard.writeText(g_rawWhois).then(() => {
btn.classList.add('copied'); btn.innerHTML = '✓ 已复制';
setTimeout(() => { btn.classList.remove('copied'); btn.innerHTML = '📋 复制'; }, 2000);
}).catch(() => {});
}

function renderWhoisCards(fields, raw) {
g_rawWhois = raw || '';
const f = fields;
const html = [];
const regDate = f.reg_date ? formatDate(f.reg_date) : '';
const expDate = f.exp_date ? formatDate(f.exp_date) : '';
let regDays = '', remainDays = '';
if (f.reg_date) {
regDays = daysBetween(f.reg_date, new Date());
if (regDays < 0) regDays = 0;
regDays = regDays.toLocaleString();
}
if (f.exp_date) {
remainDays = daysBetween(new Date(), f.exp_date);
if (remainDays < 0) remainDays = 0;
remainDays = remainDays.toLocaleString();
}

html.push('<div class="card-grid">');

html.push('<div class="card card-sm">');
html.push('<div class="card-icon blue">📋</div>');
html.push('<div class="card-title" style="font-size:15px;margin-bottom:12px">域名信息</div>');
html.push(`<div class="row"><div class="row-label">域名</div><div class="row-value">${esc(f.domain)}</div></div>`);
if (f.registrar) html.push(`<div class="row"><div class="row-label">注册商</div><div class="row-value">${esc(f.registrar)}</div></div>`);
if (regDate) html.push(`<div class="row"><div class="row-label">注册日期</div><div class="row-value">${esc(regDate)}</div></div>`);
if (expDate) html.push(`<div class="row"><div class="row-label">到期日期</div><div class="row-value">${esc(expDate)}</div></div>`);
if (regDays) html.push(`<div class="row" style="border-bottom:none"><div class="row-label">注册天数</div><div class="row-value"><span class="stat-days">${esc(regDays)}</span><span class="stat-days-label"> 天</span></div></div>`);
if (remainDays) html.push(`<div class="row" style="border-bottom:none"><div class="row-label">距离到期</div><div class="row-value"><span class="stat-days">${esc(remainDays)}</span><span class="stat-days-label"> 天</span></div></div>`);
html.push('</div>');

html.push('<div class="card card-sm">');
html.push('<div class="card-icon green">🖥</div>');
html.push('<div class="card-title" style="font-size:15px;margin-bottom:12px">DNS 服务器</div>');
if (f.nameservers && f.nameservers.length) {
f.nameservers.forEach(ns => {
html.push(`<div class="row" style="border-bottom:none"><div class="row-value">${esc(ns)}</div></div>`);
});
} else {
html.push('<div class="na">无信息</div>');
}
html.push('</div>');

html.push('<div class="card card-sm">');
html.push('<div class="card-icon orange">🔒</div>');
html.push('<div class="card-title" style="font-size:15px;margin-bottom:12px">域名状态</div>');
if (f.status && f.status.length) {
f.status.forEach(s => {
html.push(`<div class="row" style="border-bottom:1px solid #f0f2f5"><div class="row-value"><span class="tag">${esc(s)}</span></div></div>`);
});
} else {
html.push('<div class="row" style="border-bottom:none"><div class="na">无信息</div></div>');
}
html.push(`<div class="row" style="border-bottom:none"><div class="row-label">DNSSEC</div><div class="row-value">${esc(f.dnssec || 'N/A')}</div></div>`);
html.push('</div>');

html.push('<div class="card card-sm">');
html.push('<div class="card-icon purple">🏢</div>');
html.push('<div class="card-title" style="font-size:15px;margin-bottom:12px">注册商信息</div>');
if (f.registrar) html.push(`<div class="row"><div class="row-label">注册商</div><div class="row-value">${esc(f.registrar)}</div></div>`);
if (f.email) html.push(`<div class="row"><div class="row-label">联系邮箱</div><div class="row-value">${esc(f.email)}</div></div>`);
if (f.updated_date) html.push(`<div class="row" style="border-bottom:none"><div class="row-label">最后更新</div><div class="row-value">${esc(formatDate(f.updated_date))}</div></div>`);
if (!f.registrar && !f.email && !f.updated_date) html.push('<div class="na">无信息</div>');
html.push('</div>');

html.push('</div>');

const rawId = 'rawWhois_' + Date.now();
html.push('<div class="raw-section">');
html.push('<div class="raw-header"><span>📄 原始 WHOIS 数据</span><button class="copy-btn" id="copyRawBtn" onclick="copyRaw()">📋 复制</button></div>');
html.push(`<div class="raw-box" id="${rawId}">${esc(raw)}</div>`);
html.push('</div>');

return html.join('');
}

function doWhois() {
const domain = document.getElementById('domainInput').value.trim();
if (!domain) return;
const el = document.getElementById('whoisResult');
el.innerHTML = '<div class="loading"><div class="spinner"></div>查询中...</div>';
fetch('api/index.php?source=whois&domain=' + encodeURIComponent(domain))
.then(r => r.json())
.then(d => {
if (!d.success) { el.innerHTML = `<div class="error">${esc(d.error)}</div>`; return; }
el.innerHTML = renderWhoisCards(d.data.fields, d.data.raw);
})
.catch(e => { el.innerHTML = `<div class="error">查询失败：${esc(e.message)}</div>`; });
}

let g_clientIP = '';

function getClientIP() {
return fetch('api/index.php?source=myip').then(r => r.json()).then(d => {
if (d.success) g_clientIP = d.data.ip;
return d.data.ip;
}).catch(() => '');
}

function queryIP(source, ip) {
const el = document.getElementById('ipContent');
el.innerHTML = '<div class="loading"><div class="spinner"></div>正在获取...</div>';
document.querySelectorAll('.btn').forEach(b => b.classList.toggle('active', b.dataset.api === source));
const info = LABELS[source] || {};
document.getElementById('ipSourceInfo').textContent = '数据来源：' + (info.name || source) + ' | 查询：' + ip;
const ctrl = new AbortController();
const t = setTimeout(() => ctrl.abort(), 15000);
fetch('api/index.php?source=' + encodeURIComponent(source) + '&query=' + encodeURIComponent(ip), { signal: ctrl.signal })
.then(r => r.json()).then(d => { clearTimeout(t); renderIP(d); })
.catch(e => { clearTimeout(t); el.innerHTML = `<div class="error">请求失败：${esc(e.message)}</div>`; });
}

checkUser();

function loadCategories(){fetch('api/index.php?source=cat_list').then(r=>r.json()).then(function(d){if(!d.success||!d.data.length)return;var top=d.data.filter(function(c){return c.mode==='top'});var list=d.data.filter(function(c){return c.mode==='list'});var el=document.getElementById('categoryNav');if(el){var h='';top.forEach(function(c){h+='<button class="nav-link" onclick="alert(\''+esc(c.name)+'\')">'+esc(c.name)+'</button>'});if(list.length){h+='<span style="border-left:1px solid #e0e4ea;height:20px;margin:0 4px"></span>';list.forEach(function(c){h+='<button class="nav-link" onclick="alert(\''+esc(c.name)+'\')">'+esc(c.name)+'</button>'})}el.innerHTML=h;el.style.display=''}}).catch(function(){})}
loadCategories();

document.querySelectorAll('.btn').forEach(btn => { btn.addEventListener('click', () => fetchAPI(btn.dataset.api)); });

getClientIP().then(ip => {
g_clientIP = ip;
queryIP('ip-api', ip);
fetch('api/index.php?source=ip-api&query=' + encodeURIComponent(ip)).then(r => r.json()).then(pos => {
  if (pos.success && (pos.data.district || pos.data.city)) {
      const wCity = pos.data.district || pos.data.city;
      fetchWeather(wCity).then(d => { renderIPWeather(d); renderWeatherPage(d); }).catch(() => {});
      document.getElementById('cityInput').placeholder = '输入城市名，如：' + wCity;
}
}).catch(() => {});
});
</script>
<div id="linkFooter" style="display:none;border-top:1px solid #e8ecf1;background:#fff;padding:20px;margin-top:40px">
<div style="max-width:900px;margin:0 auto">
<div style="font-size:13px;font-weight:600;color:#888;margin-bottom:12px">🔗 友情链接</div>
<div id="linkList" style="display:flex;flex-wrap:wrap;gap:10px"></div>
</div>
</div>
<script>
function loadFooterLinks(){fetch('api/index.php?source=link_list').then(r=>r.json()).then(function(d){if(!d.success||!d.data.length)return;document.getElementById('linkFooter').style.display='';var el=document.getElementById('linkList');el.innerHTML=d.data.map(function(x){return '<a href="'+esc(x.url)+'" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border:1px solid #e0e4ea;border-radius:8px;font-size:13px;color:#555;text-decoration:none;transition:all .15s" onmouseover="this.style.borderColor=\'#4f6af5\';this.style.color=\'#4f6af5\'" onmouseout="this.style.borderColor=\'#e0e4ea\';this.style.color=\'#555\'">'+esc(x.name)+'</a>'}).join('')}).catch(function(){})}
loadFooterLinks();
</script>
</body>
</html>
