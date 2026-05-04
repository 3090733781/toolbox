<?php if (empty($_SESSION['admin'])) return; ?>
<div id="saveMsg" class="msg">✓ 操作成功</div>

<div class="card">
<div class="card-title">🔑 我的 API 密钥</div>
<p style="font-size:13px;color:#888;margin-bottom:16px">管理你的 API Key，用于外部程序调用工具箱接口</p>
<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
<button class="btn btn-primary" onclick="showCreateModal()">➕ 创建新 Key</button>
<button class="btn btn-sm" onclick="loadMyKeys()">🔄 刷新</button>
</div>
<div id="myKeyStats" class="stats"></div>
<div id="myKeyTableWrap"><div class="loading"><div class="spinner"></div>加载中...</div></div>
</div>

<div class="card">
<div class="card-title">📊 调用记录 <button class="btn btn-sm" onclick="loadMyLogs()">🔄 刷新</button></div>
<div id="myLogWrap"><div class="loading"><div class="spinner"></div>加载中...</div></div>
</div>

<div class="modal-overlay" id="createModal">
<div class="modal" style="max-width:460px">
<h2 style="font-size:16px;margin-bottom:16px">创建新 API Key</h2>
<div class="form-group"><label>名称</label><input type="text" id="cName" placeholder="例如：我的博客" value="My Key"></div>
<div class="form-group"><label>权限</label><input type="text" id="cPerm" value="*" placeholder="* 或 ip,whois,weather"><div class="form-hint">* 全部权限，或指定接口名如 ip,whois,weather，支持 ip-* 通配</div></div>
<div class="form-row">
<div class="form-group"><label>限流（次/分钟）</label><input type="number" id="cRate" value="60" placeholder="0=不限"></div>
<div class="form-group"><label>过期时间</label><input type="datetime-local" id="cExpire"><div class="form-hint">留空永不过期</div></div>
</div>
<div style="display:flex;gap:8px;margin-top:12px">
<button class="btn btn-primary" onclick="doCreateKey()">创建</button>
<button class="btn btn-sm" onclick="document.getElementById('createModal').style.display='none'" style="border:1px solid #d0d5dd">取消</button>
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

function loadMyKeys(){
  el('myKeyTableWrap').innerHTML='<div class="loading"><div class="spinner"></div>加载中...</div>';
  fetch('../api/index.php?source=apikey_list').then(r=>r.json()).then(d=>{
    if(!d.success){el('myKeyTableWrap').innerHTML='<div class="err">'+esc(d.error)+'</div>';return}
    renderMyKeys(d.data);
  }).catch(()=>{el('myKeyTableWrap').innerHTML='<div class="err">加载失败</div>'});
  fetch('../api/index.php?source=apikey_stats').then(r=>r.json()).then(d=>{
    if(!d.success)return;
    el('myKeyStats').innerHTML=
      '<div class="stat"><div class="stat-num">'+d.data.key_count+'</div><div class="stat-label">密钥数</div></div>'+
      '<div class="stat"><div class="stat-num">'+d.data.total_calls+'</div><div class="stat-label">总调用</div></div>'+
      '<div class="stat"><div class="stat-num">'+d.data.today_calls+'</div><div class="stat-label">今日</div></div>'+
      '<div class="stat"><div class="stat-num">'+d.data.success_calls+'</div><div class="stat-label">成功</div></div>';
  }).catch(()=>{});
}

function renderMyKeys(keys){
  if(!keys.length){el('myKeyTableWrap').innerHTML='<div style="text-align:center;padding:30px;color:#888">你还没有 API Key，点击上方创建</div>';return}
  let h='<table><thead><tr><th>名称</th><th>Key</th><th>权限</th><th>限流</th><th>状态</th><th>调用</th><th>最后使用</th><th>操作</th></tr></thead><tbody>';
  keys.forEach(k=>{
    const active=k.status==1;
    h+='<tr><td>'+esc(k.name)+'</td>';
    h+='<td style="font-family:monospace;font-size:11px;max-width:160px;overflow:hidden;text-overflow:ellipsis" title="'+esc(k.api_key)+'">'+esc(k.api_key)+'</td>';
    h+='<td><span style="font-size:11px">'+esc((k.permissions||'*').substring(0,12))+'</span></td>';
    h+='<td>'+k.rate_limit+'/分</td>';
    h+='<td><button class="toggle '+(active?'on':'')+'" onclick="toggleMyKey('+k.id+')"></button></td>';
    h+='<td>'+(k.used_count||0)+'</td>';
    h+='<td style="font-size:11px">'+(k.last_used_at||'-')+'</td>';
    h+='<td style="white-space:nowrap"><button class="btn btn-sm" onclick="editMyKey('+k.id+')">✏️</button> <button class="btn btn-sm" onclick="deleteMyKey('+k.id+')" style="border-color:#fecaca;color:#e74c3c">🗑</button></td>';
    h+='</tr>';
  });
  h+='</tbody></table>';
  el('myKeyTableWrap').innerHTML=h;
}

function toggleMyKey(id){
  fetch('../api/index.php?source=apikey_toggle&id='+id).then(r=>r.json()).then(d=>{
    if(d.success)loadMyKeys();else alert(d.error)
  }).catch(()=>alert('操作失败'));
}

function deleteMyKey(id){
  if(!confirm('确定删除该 API Key？使用了该 Key 的应用将无法访问。'))return;
  fetch('../api/index.php?source=apikey_delete&id='+id).then(r=>r.json()).then(d=>{
    if(d.success){showMsg('已删除');loadMyKeys()}else alert(d.error)
  }).catch(()=>alert('操作失败'));
}

function showCreateModal(){el('createModal').style.display='flex'}

function doCreateKey(){
  const name=el('cName').value.trim();
  if(!name){alert('请输入 Key 名称');return}
  fetch('../api/index.php?source=apikey_create',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({name:name,permissions:el('cPerm').value.trim(),rate_limit:parseInt(el('cRate').value)||60,expires_at:el('cExpire').value||null})
  }).then(r=>r.json()).then(d=>{
    if(d.success){
      el('createModal').style.display='none';
      showMsg('Key 已创建：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">'+esc(d.data.api_key)+'</code><br><span style="font-size:12px;color:#e74c3c">请立即复制保存，关闭后将无法再次查看完整密钥</span>');
      loadMyKeys();
    } else alert(d.error)
  }).catch(()=>alert('操作失败'));
}

function editMyKey(id){
  el('createModal').style.display='none';
  fetch('../api/index.php?source=apikey_list').then(r=>r.json()).then(d=>{
    if(!d.success)return;
    const k=d.data.find(x=>x.id==id);
    if(!k)return;
    el('eId').value=k.id;
    el('eName').value=k.name;
    el('ePerm').value=k.permissions||'*';
    el('eRate').value=k.rate_limit||0;
    el('editModal').style.display='flex';
  }).catch(()=>{});
}

function doEditKey(){
  fetch('../api/index.php?source=apikey_update',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id:parseInt(el('eId').value),name:el('eName').value.trim(),permissions:el('ePerm').value.trim(),rate_limit:parseInt(el('eRate').value)||0})
  }).then(r=>r.json()).then(d=>{
    if(d.success){el('editModal').style.display='none';showMsg('已更新');loadMyKeys()}
    else alert(d.error)
  }).catch(()=>alert('操作失败'));
}

function loadMyLogs(){
  el('myLogWrap').innerHTML='<div class="loading"><div class="spinner"></div>加载中...</div>';
  fetch('../api/index.php?source=apikey_logs&limit=30').then(r=>r.json()).then(d=>{
    if(!d.success||!d.data.logs.length){el('myLogWrap').innerHTML='<div style="text-align:center;padding:20px;color:#888">暂无调用记录</div>';return}
    let h='<table><thead><tr><th>时间</th><th>密钥</th><th>接口</th><th>IP</th><th>状态</th></tr></thead><tbody>';
    d.data.logs.forEach(l=>{
      h+='<tr><td style="font-size:11px">'+esc(l.created_at)+'</td><td style="font-size:11px">'+esc(l.key_name||'-')+'</td>';
      h+='<td><span class="badge" style="background:#f0f2ff;color:#4f6af5">'+esc(l.source)+'</span></td>';
      h+='<td style="font-size:11px">'+esc(l.ip||'-')+'</td>';
      h+='<td><span class="tag '+(l.status==='ok'?'tag-on':'tag-off')+'">'+esc(l.status)+'</span></td></tr>';
    });
    h+='</tbody></table>';
    el('myLogWrap').innerHTML=h;
  }).catch(()=>{el('myLogWrap').innerHTML='<div class="err">加载失败</div>'});
}

function showMsg(t){const m=el('saveMsg');m.innerHTML=t;m.style.display='block';setTimeout(()=>{m.style.display='none'},5000)}
function esc(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]})}

loadMyKeys();
loadMyLogs();
</script>
<div class="modal-overlay" id="editModal">
<div class="modal" style="max-width:420px">
<h2 style="font-size:16px;margin-bottom:16px">编辑 API Key</h2>
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
