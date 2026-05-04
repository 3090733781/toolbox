<?php if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') return; ?>
<div id="saveMsg" class="msg">✓ 操作成功</div>

<div class="card">
<div class="card-title">🔑 API Key 管理</div>
<p style="font-size:13px;color:#888;margin-bottom:16px">管理所有用户的 API Key，支持限流、权限控制、调用统计</p>
<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
<button class="btn btn-primary" onclick="showCreateModal()">➕ 创建新 Key</button>
<button class="btn btn-sm" onclick="loadKeys()">🔄 刷新</button>
</div>
<div id="keyStats" class="stats"></div>
<div id="keyTableWrap"><div class="loading"><div class="spinner"></div>加载中...</div></div>
</div>

<div class="card">
<div class="card-title">📊 调用日志 <button class="btn btn-sm" onclick="loadAllLogs()">🔄 刷新</button></div>
<div id="logWrap"><div class="loading"><div class="spinner"></div>加载中...</div></div>
</div>

<div class="modal-overlay" id="createModal">
<div class="modal" style="max-width:480px">
<h2 style="font-size:16px;margin-bottom:16px">创建 API Key</h2>
<div class="form-group"><label>所属用户 ID</label><input type="number" id="cUser" placeholder="输入用户 ID" value="1"></div>
<div class="form-group"><label>Key 名称</label><input type="text" id="cName" placeholder="例如：我的应用" value="New Key"></div>
<div class="form-group"><label>权限</label><input type="text" id="cPerm" value="*" placeholder="* 或 ip,whois,weather"><div class="form-hint">* 表示全部权限，逗号分隔指定接口名，支持通配符 ip-*</div></div>
<div class="form-row">
<div class="form-group"><label>限流（次/分钟）</label><input type="number" id="cRate" value="60" placeholder="0=不限"></div>
<div class="form-group"><label>过期时间</label><input type="datetime-local" id="cExpire"><div class="form-hint">留空=永不过期</div></div>
</div>
<div style="display:flex;gap:8px;margin-top:12px">
<button class="btn btn-primary" onclick="doCreateKey()">创建</button>
<button class="btn btn-sm" onclick="document.getElementById('createModal').style.display='none'" style="border:1px solid #d0d5dd">取消</button>
</div>
</div>
</div>

<div class="modal-overlay" id="editModal">
<div class="modal" style="max-width:460px">
<h2 style="font-size:16px;margin-bottom:16px">编辑 Key</h2>
<input type="hidden" id="eId">
<div class="form-group"><label>名称</label><input type="text" id="eName"></div>
<div class="form-group"><label>权限</label><input type="text" id="ePerm"><div class="form-hint">* 或 ip,whois,weather</div></div>
<div class="form-group"><label>限流（次/分钟）</label><input type="number" id="eRate"></div>
<div style="display:flex;gap:8px;margin-top:12px">
<button class="btn btn-primary" onclick="doEditKey()">保存</button>
<button class="btn btn-sm" onclick="document.getElementById('editModal').style.display='none'" style="border:1px solid #d0d5dd">取消</button>
</div>
</div>
</div>

<style>
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center}
.modal-overlay.show{display:flex}
.modal{background:#fff;border-radius:12px;padding:24px;width:400px;max-width:90vw}
.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
.tag-on{background:#f0fdf4;color:#16a34a}
.tag-off{background:#fef2f2;color:#e74c3c}
</style>

<script>
function el(id){return document.getElementById(id)}

function loadKeys(){
  el('keyTableWrap').innerHTML = '<div class="loading"><div class="spinner"></div>加载中...</div>';
  fetch('../api/index.php?source=apikey_list_all').then(r=>r.json()).then(d=>{
    if (!d.success){el('keyTableWrap').innerHTML='<div class="err">'+esc(d.error)+'</div>';return}
    renderKeys(d.data);
  }).catch(()=>{el('keyTableWrap').innerHTML='<div class="err">加载失败</div>'});
}

function renderKeys(keys){
  if (!keys.length){el('keyTableWrap').innerHTML='<div style="text-align:center;padding:30px;color:#888">暂无 API Key</div>';return}
  let h='<table><thead><tr><th>ID</th><th>用户</th><th>名称</th><th>Key</th><th>权限</th><th>限流</th><th>状态</th><th>调用次数</th><th>最后使用</th><th>操作</th></tr></thead><tbody>';
  keys.forEach(k=>{
    const active=k.status==1;
    h+='<tr><td>'+k.id+'</td><td>'+esc(k.username||'用户#'+k.user_id)+'</td><td>'+esc(k.name)+'</td><td style="font-family:monospace;font-size:11px;max-width:160px;overflow:hidden;text-overflow:ellipsis">'+esc(k.api_key)+'</td>';
    h+='<td><span style="font-size:11px">'+esc((k.permissions||'*').substring(0,12))+'</span></td>';
    h+='<td>'+k.rate_limit+'/分</td>';
    h+='<td><button class="toggle '+(active?'on':'')+'" onclick="toggleKey('+k.id+')"></button></td>';
    h+='<td>'+(k.used_count||0)+'</td>';
    h+='<td style="font-size:11px">'+(k.last_used_at||'-')+'</td>';
    h+='<td style="white-space:nowrap"><button class="btn btn-sm" onclick="showEditModal('+k.id+')">✏️</button> <button class="btn btn-sm" onclick="deleteKey('+k.id+')" style="border-color:#fecaca;color:#e74c3c">🗑</button></td>';
    h+='</tr>';
  });
  h+='</tbody></table>';
  el('keyTableWrap').innerHTML=h;
}

function toggleKey(id){
  fetch('../api/index.php?source=apikey_admin_toggle&id='+id).then(r=>r.json()).then(d=>{
    if(d.success){loadKeys()}else{alert(d.error)}
  }).catch(()=>alert('操作失败'));
}

function deleteKey(id){
  if(!confirm('确定删除该 API Key？'))return;
  fetch('../api/index.php?source=apikey_admin_delete&id='+id).then(r=>r.json()).then(d=>{
    if(d.success){showMsg('已删除');loadKeys()}else{alert(d.error)}
  }).catch(()=>alert('操作失败'));
}

function showCreateModal(){el('createModal').style.display='flex'}
function showEditModal(id){
  el('createModal').style.display='none';
  const row=document.querySelector('#keyTableWrap table tbody tr:nth-child('+(id+1)+')');
  el('eId').value=id;
  // 直接从 API 获取数据
  fetch('../api/index.php?source=apikey_list_all').then(r=>r.json()).then(d=>{
    if(!d.success)return;
    const k=d.data.find(x=>x.id==id);
    if(!k)return;
    el('eName').value=k.name;
    el('ePerm').value=k.permissions||'*';
    el('eRate').value=k.rate_limit||0;
    el('editModal').style.display='flex';
  }).catch(()=>{});
}

function doEditKey(){
  const id=el('eId').value;
  fetch('../api/index.php?source=apikey_update',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({
      id:id,name:el('eName').value.trim(),
      permissions:el('ePerm').value.trim(),
      rate_limit:parseInt(el('eRate').value)||0
    })
  }).then(r=>r.json()).then(d=>{
    if(d.success){el('editModal').style.display='none';showMsg('已更新');loadKeys()}
    else alert(d.error)
  }).catch(()=>alert('操作失败'));
}

function doCreateKey(){
  const uid=parseInt(el('cUser').value)||0;
  if(uid<=0){alert('请输入有效的用户 ID');return}
  fetch('../api/index.php?source=apikey_admin_create',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({
      user_id:uid,name:el('cName').value.trim(),
      permissions:el('cPerm').value.trim(),
      rate_limit:parseInt(el('cRate').value)||60,
      expires_at:el('cExpire').value||null
    })
  }).then(r=>r.json()).then(d=>{
    if(d.success){
      el('createModal').style.display='none';
      showMsg('Key 已创建：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">'+esc(d.data.api_key)+'</code>');
      loadKeys();
    } else alert(d.error)
  }).catch(()=>alert('操作失败'));
}

function loadAllLogs(){
  el('logWrap').innerHTML='<div class="loading"><div class="spinner"></div>加载中...</div>';
  fetch('../api/index.php?source=apikey_logs_all&limit=30').then(r=>r.json()).then(d=>{
    if(!d.success||!d.data.logs.length){el('logWrap').innerHTML='<div style="text-align:center;padding:20px;color:#888">暂无调用日志</div>';return}
    let h='<table><thead><tr><th>时间</th><th>用户</th><th>Key</th><th>接口</th><th>IP</th><th>状态</th><th>错误</th></tr></thead><tbody>';
    d.data.logs.forEach(l=>{
      h+='<tr><td style="font-size:11px">'+esc(l.created_at)+'</td><td>'+esc(l.username||'-')+'</td><td style="font-size:11px">'+esc(l.key_name||'-')+'</td>';
      h+='<td><span class="badge" style="background:#f0f2ff;color:#4f6af5">'+esc(l.source)+'</span></td>';
      h+='<td style="font-size:11px">'+esc(l.ip||'-')+'</td>';
      h+='<td><span class="tag '+(l.status==='ok'?'tag-on':'tag-off')+'">'+esc(l.status)+'</span></td>';
      h+='<td style="font-size:11px;color:#e74c3c;max-width:200px;overflow:hidden;text-overflow:ellipsis">'+esc(l.error||'-')+'</td></tr>';
    });
    h+='</tbody></table>';
    h+='<div style="text-align:center;margin-top:10px;font-size:12px;color:#888">共 '+d.data.total+' 条记录</div>';
    el('logWrap').innerHTML=h;
  }).catch(()=>{el('logWrap').innerHTML='<div class="err">加载失败</div>'});
}

function showMsg(t){const m=el('saveMsg');m.innerHTML=t;m.style.display='block';setTimeout(()=>{m.style.display='none'},3000)}

function esc(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]})}

loadKeys();
loadAllLogs();
</script>
