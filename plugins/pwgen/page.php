<!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>🔑 密码生成器</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:16px;padding:36px;max-width:480px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.15)}
h1{font-size:22px;font-weight:800;color:#1a1a2e;text-align:center;margin-bottom:6px}
.sub{font-size:13px;color:#888;text-align:center;margin-bottom:24px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:6px}
.form-group input[type=number]{width:100%;padding:10px 14px;border:2px solid #e0e4ea;border-radius:8px;font-size:14px;outline:none}
.form-group input:focus{border-color:#4f6af5}
.options{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px}
.opt{display:flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #e0e4ea;border-radius:8px;cursor:pointer;font-size:13px;color:#555}
.opt:hover{border-color:#4f6af5}
.opt input{width:16px;height:16px;cursor:pointer}
.btn{width:100%;padding:12px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;background:linear-gradient(135deg,#4f6af5,#764ba2);color:#fff}
.btn:hover{opacity:.9}
.result{margin-top:20px;display:none}
.result-box{background:#f8f9fb;border:1px solid #e8ecf1;border-radius:8px;padding:16px;font-family:monospace;font-size:18px;text-align:center;word-break:break-all;color:#333}
.strength{text-align:center;margin-top:8px;font-size:13px;color:#888}
.strength span{font-weight:600}
.copy-btn{width:100%;padding:10px;border:1px solid #d0d5dd;border-radius:8px;background:#fff;color:#555;font-size:13px;cursor:pointer;margin-top:8px}
.copy-btn:hover{border-color:#4f6af5;color:#4f6af5}
.back{text-align:center;margin-top:16px}
.back a{color:#888;font-size:12px;text-decoration:none}
.back a:hover{color:#4f6af5}
</style></head>
<body>
<div class="card">
<h1>🔑 密码生成器</h1>
<p class="sub">生成高强度随机密码</p>
<div class="form-group"><label>密码长度</label><input type="number" id="pwLen" value="16" min="4" max="128"></div>
<div class="options">
<label class="opt"><input type="checkbox" id="pwUpper" checked> 大写字母</label>
<label class="opt"><input type="checkbox" id="pwLower" checked> 小写字母</label>
<label class="opt"><input type="checkbox" id="pwDigits" checked> 数字</label>
<label class="opt"><input type="checkbox" id="pwSymbols" checked> 符号</label>
</div>
<button class="btn" onclick="generatePw()">🔄 生成密码</button>
<div class="result" id="pwResult">
<div class="result-box" id="pwDisplay"></div>
<div class="strength">强度：<span id="pwStrength"></span></div>
<button class="copy-btn" onclick="copyPw()">📋 复制密码</button>
</div>
<div class="back"><a href="javascript:history.back()">← 返回</a></div>
</div>
<script>
function generatePw(){var l=document.getElementById('pwLen').value,u=document.getElementById('pwUpper').checked?1:0,lo=document.getElementById('pwLower').checked?1:0,d=document.getElementById('pwDigits').checked?1:0,s=document.getElementById('pwSymbols').checked?1:0;fetch('/api/index.php?source=pwgen_generate&len='+l+'&upper='+u+'&lower='+lo+'&digits='+d+'&symbols='+s).then(function(r){return r.json()}).then(function(r){if(!r.success){alert(r.error);return}document.getElementById('pwDisplay').textContent=r.data.password;document.getElementById('pwStrength').textContent=r.data.strength;document.getElementById('pwResult').style.display='block'})}
function copyPw(){var p=document.getElementById('pwDisplay').textContent;if(navigator.clipboard){navigator.clipboard.writeText(p).then(function(){alert('已复制')})}else{prompt('复制密码：',p)}}
generatePw();
</script>
</body>
</html>
