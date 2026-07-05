<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>二次验证登录</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',sans-serif;background:#f4f6f8;color:#1f2937;min-height:100vh;padding:24px}
.wrap{max-width:920px;margin:0 auto}
.hero{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:16px}
.hero h1{font-size:24px;margin-bottom:6px;color:#111827}
.hero p{font-size:14px;color:#6b7280;line-height:1.7}
.grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,360px);gap:16px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px}
.title{font-size:17px;font-weight:700;margin-bottom:14px;color:#111827}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px}
label{display:block;font-size:13px;font-weight:600;color:#4b5563;margin-bottom:5px}
input,textarea{width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:14px;outline:none;background:#fff;color:#111827}
input:focus,textarea:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
textarea{min-height:84px;resize:vertical;font-family:Consolas,'Courier New',monospace}
.btns{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
button{border:0;border-radius:8px;padding:10px 14px;font-size:13px;font-weight:700;cursor:pointer;background:#2563eb;color:#fff}
button:hover{background:#1d4ed8}
button.secondary{background:#fff;color:#374151;border:1px solid #d1d5db}
button.secondary:hover{background:#f9fafb}
.result{display:none;margin-top:14px;border-top:1px solid #eef2f7;padding-top:14px}
.secret{font-family:Consolas,'Courier New',monospace;font-size:15px;word-break:break-all;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:12px}
.qr{width:220px;height:220px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;margin:10px auto}
.qr img{width:200px;height:200px}
.hint{font-size:12px;color:#6b7280;line-height:1.7;margin-top:10px}
.status{margin-top:12px;padding:10px 12px;border-radius:8px;font-size:13px;display:none}
.status.ok{display:block;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}
.status.err{display:block;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.steps{display:grid;gap:10px}
.step{display:flex;gap:10px;align-items:flex-start;padding:12px;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa}
.num{width:24px;height:24px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex:0 0 auto}
.step div:last-child{font-size:13px;line-height:1.65;color:#4b5563}
@media(max-width:760px){body{padding:14px}.grid{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}.hero h1{font-size:21px}}
</style>
</head>
<body>
<div class="wrap">
  <section class="hero">
    <h1>🔐 二次验证登录</h1>
    <p>生成身份验证器 App 可扫描的 TOTP 密钥，并校验 6 位动态验证码。适用于给登录流程增加二次验证前的配置和测试。</p>
  </section>

  <div class="grid">
    <main class="card">
      <div class="title">生成验证器密钥</div>
      <div class="form-row">
        <div>
          <label for="issuer">站点名称</label>
          <input id="issuer" value="工具箱" maxlength="40">
        </div>
        <div>
          <label for="account">账号标识</label>
          <input id="account" value="admin" maxlength="80">
        </div>
      </div>
      <div class="btns">
        <button onclick="generateSecret()">生成密钥</button>
        <button class="secondary" onclick="copySecret()">复制密钥</button>
        <button class="secondary" onclick="copyOtpUrl()">复制绑定链接</button>
      </div>

      <div class="result" id="result">
        <label>密钥</label>
        <div class="secret" id="secretText"></div>
        <div class="qr" id="qrBox"></div>
        <label for="otpUrl">绑定链接</label>
        <textarea id="otpUrl" readonly></textarea>
        <p class="hint">推荐使用 Microsoft Authenticator、Google Authenticator、1Password、Bitwarden 等支持 TOTP 的应用扫描二维码。</p>
      </div>

      <div style="margin-top:18px">
        <div class="title">校验动态验证码</div>
        <div class="form-row">
          <div>
            <label for="verifySecret">密钥</label>
            <input id="verifySecret" placeholder="粘贴 Base32 密钥">
          </div>
          <div>
            <label for="verifyCode">6 位验证码</label>
            <input id="verifyCode" inputmode="numeric" maxlength="6" placeholder="000000">
          </div>
        </div>
        <div class="btns"><button onclick="verifyCode()">验证</button></div>
        <div class="status" id="verifyStatus"></div>
      </div>
    </main>

    <aside class="card">
      <div class="title">接入步骤</div>
      <div class="steps">
        <div class="step"><span class="num">1</span><div>为用户生成并保存密钥，登录账号密码通过后再要求输入动态验证码。</div></div>
        <div class="step"><span class="num">2</span><div>验证时允许前后 1 个 30 秒窗口，可以容忍手机和服务器的轻微时间差。</div></div>
        <div class="step"><span class="num">3</span><div>密钥只展示一次；正式启用时建议加密保存，并提供备用恢复码。</div></div>
      </div>
      <p class="hint">当前插件提供生成和校验能力，不会自动修改主站登录流程。</p>
    </aside>
  </div>
</div>

<script>
var pageUrl = window.location.href.split('#')[0].split('?')[0];
var apiUrls = [];
if (pageUrl.indexOf('/plugins/two_factor_login/page.php') !== -1) {
  apiUrls.push(pageUrl.replace('/plugins/two_factor_login/page.php', '/api/index.php'));
  apiUrls.push(pageUrl.replace('/page.php', '/index.php'));
}
apiUrls.push('index.php');
var currentOtpUrl = '';

function requestJson(path, options, index) {
  index = index || 0;
  var base = apiUrls[index];
  var join = path.indexOf('?') === 0 ? '' : '?';
  return fetch(base + join + path, options).then(function(r) {
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.text();
  }).then(function(text) {
    try {
      var data = JSON.parse(text);
      if (data && data.success === false && index + 1 < apiUrls.length) {
        var msg = String(data.error || '');
        if (/install|安装|权限|登录|permission|forbidden/i.test(msg)) {
          return requestJson(path, options, index + 1);
        }
      }
      return data;
    }
    catch (e) { throw new Error(text ? text.slice(0, 120) : '空响应'); }
  }).catch(function(e) {
    if (index + 1 < apiUrls.length) return requestJson(path, options, index + 1);
    throw e;
  });
}

function setStatus(text, ok) {
  var el = document.getElementById('verifyStatus');
  el.textContent = text;
  el.className = 'status ' + (ok ? 'ok' : 'err');
}

function copyText(text) {
  if (!text) return;
  if (navigator.clipboard) navigator.clipboard.writeText(text);
  else prompt('复制内容', text);
}

function generateSecret() {
  var issuer = document.getElementById('issuer').value.trim() || '工具箱';
  var account = document.getElementById('account').value.trim() || 'admin';
  var path = '?source=twofa_generate&issuer=' + encodeURIComponent(issuer) + '&account=' + encodeURIComponent(account);
  requestJson(path).then(function(d) {
    if (!d.success) throw new Error(d.error || '生成失败');
    var secret = d.data.secret;
    currentOtpUrl = d.data.otp_auth_url;
    document.getElementById('secretText').textContent = secret;
    document.getElementById('verifySecret').value = secret;
    document.getElementById('otpUrl').value = currentOtpUrl;
    document.getElementById('qrBox').innerHTML = '<img alt="TOTP QR Code" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(currentOtpUrl) + '">';
    document.getElementById('result').style.display = 'block';
  }).catch(function(e) { alert('生成失败：' + (e.message || '网络错误')); });
}

function copySecret() {
  copyText(document.getElementById('secretText').textContent);
}

function copyOtpUrl() {
  copyText(currentOtpUrl || document.getElementById('otpUrl').value);
}

function verifyCode() {
  var secret = document.getElementById('verifySecret').value.trim();
  var code = document.getElementById('verifyCode').value.trim();
  if (!secret || !code) {
    setStatus('请输入密钥和验证码', false);
    return;
  }
  requestJson('?source=twofa_verify', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({secret, code})
  }).then(function(d) {
    if (d.success && d.data && d.data.valid) setStatus('验证通过，动态验证码有效', true);
    else setStatus(d.error || '验证码无效或已过期', false);
  }).catch(function(e) { setStatus('请求失败：' + (e.message || '网络错误'), false); });
}

generateSecret();
</script>
</body>
</html>
