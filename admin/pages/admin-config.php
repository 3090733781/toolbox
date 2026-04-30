<?php if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') return; ?>
<div class="card"><div class="card-title">🔐 管理员配置</div>
<div class="form-group"><label>当前管理员</label><input type="text" id="adminUser" value="admin" readonly style="width:100%;padding:9px 12px;border:1px solid #d0d5dd;border-radius:6px;font-size:13px;outline:none;background:#f8f9fb"></div>
<div class="form-group"><label>新密码</label><input type="password" id="adminNewPwd" placeholder="输入新密码（留空不修改）" style="width:100%;padding:9px 12px;border:1px solid #d0d5dd;border-radius:6px;font-size:13px;outline:none"><div class="form-hint">输入新密码后点击修改</div></div>
<button class="btn btn-primary" onclick="changeAdminPwd()">修改密码</button></div>
<script>
function changeAdminPwd(){var p=document.getElementById('adminNewPwd').value;if(!p||p.length<4){alert('密码至少4位');return}fetch('../api/index.php?source=admin_save',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({password:p})}).then(r=>r.json()).then(function(d){if(d.success){alert('密码已修改');document.getElementById('adminNewPwd').value=''}else alert('修改失败：'+(d.error||''))}).catch(function(){alert('网络错误')})}
</script>
