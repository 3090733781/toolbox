<?php if (empty($_SESSION['admin'])) return; ?>
<div id="saveMsg" class="msg">✓ 操作成功</div>

<div class="card">
<div class="card-title">📁 我的文件</div>
<p style="font-size:13px;color:#888;margin-bottom:16px">查看和管理你上传的文件</p>
<div style="display:flex;gap:10px;margin-bottom:16px">
<button class="btn btn-sm" onclick="loadMyFiles()">🔄 刷新</button>
</div>
<div id="myFileStats" class="stats"></div>
<div id="myFileList"><div class="loading"><div class="spinner"></div>加载中...</div></div>
</div>

<script>
function el(id){return document.getElementById(id)}

function loadMyFiles(){
  el('myFileList').innerHTML='<div class="loading"><div class="spinner"></div>加载中...</div>';
  fetch('../api/index.php?source=file_list').then(r=>r.json()).then(d=>{
    if(!d.success){el('myFileList').innerHTML='<div class="err">'+esc(d.error)+'</div>';return}
    const myUserId=g_myUserId||0;
    const myFiles=d.data.filter(function(f){return f.user_id==myUserId});
    renderMyFiles(myFiles);
  }).catch(()=>{el('myFileList').innerHTML='<div class="err">加载失败</div>'});
}

function renderMyFiles(files){
  if(!files.length){
    el('myFileList').innerHTML='<div style="text-align:center;padding:40px;color:#888">你还没有上传过文件</div>';
    el('myFileStats').innerHTML='<div class="stat"><div class="stat-num">0</div><div class="stat-label">文件数</div></div><div class="stat"><div class="stat-num">0 B</div><div class="stat-label">总大小</div></div>';
    return;
  }
  let totalSize=0;
  let h='<table><thead><tr><th>文件名</th><th>大小</th><th>上传时间</th><th>操作</th></tr></thead><tbody>';
  files.forEach(function(f){
    totalSize+=f.size;
    h+='<tr><td style="max-width:200px;overflow:hidden;text-overflow:ellipsis" title="'+esc(f.name)+'">'+esc(f.name)+'</td>';
    h+='<td>'+formatSize(f.size)+'</td>';
    h+='<td style="font-size:11px">'+new Date(f.time*1000).toLocaleString('zh-CN')+'</td>';
    h+='<td style="white-space:nowrap"><a class="btn btn-sm" href="../api/index.php?source=file_download&file='+encodeURIComponent(f.name)+'" download>⬇ 下载</a> <button class="btn btn-sm" onclick="deleteMyFile(\''+esc(f.name)+'\')" style="border-color:#fecaca;color:#e74c3c">🗑 删除</button></td>';
    h+='</tr>';
  });
  h+='</tbody></table>';
  el('myFileList').innerHTML=h;
  el('myFileStats').innerHTML='<div class="stat"><div class="stat-num">'+files.length+'</div><div class="stat-label">文件数</div></div><div class="stat"><div class="stat-num">'+formatSize(totalSize)+'</div><div class="stat-label">总大小</div></div>';
}

function deleteMyFile(name){
  if(!confirm('确定删除「'+name+'」？'))return;
  fetch('../api/index.php?source=file_delete&file='+encodeURIComponent(name))
    .then(r=>r.json()).then(d=>{
      if(d.success){showMsg('已删除');loadMyFiles()}
      else alert('删除失败：'+d.error)
    }).catch(()=>alert('删除失败'));
}

function formatSize(b){
  if(b===0)return'0 B';
  const u=['B','KB','MB','GB'];let i=0;
  while(b>=1024&&i<u.length-1){b/=1024;i++}
  return (i>0?b.toFixed(1):b)+' '+u[i];
}

function showMsg(t){const m=el('saveMsg');m.innerHTML=t;m.style.display='block';setTimeout(()=>{m.style.display='none'},3000)}
function esc(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]})}

var g_myUserId=0;
fetch('../api/index.php?source=user_info').then(r=>r.json()).then(d=>{if(d.success)g_myUserId=d.data.id;loadMyFiles()}).catch(()=>{});
</script>
