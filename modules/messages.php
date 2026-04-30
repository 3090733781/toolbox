<div class="hero"><h1>💬 留言板</h1><p>留下您的意见或建议</p></div>

<div id="msgPostArea">
<div class="card">
<div class="card-title">发表留言</div>
<div style="display:flex;gap:10px;margin-bottom:10px">
<input type="text" id="msgName" placeholder="您的昵称" value="访客" style="flex:1;padding:10px 14px;border:2px solid #e0e4ea;border-radius:8px;font-size:14px;outline:none" readonly>
</div>
<textarea id="msgContent" placeholder="输入留言内容..." style="width:100%;min-height:100px;padding:12px 14px;border:2px solid #e0e4ea;border-radius:8px;font-size:14px;outline:none;resize:vertical;font-family:inherit"></textarea>
<div style="margin-top:10px"><button class="btn btn-primary" onclick="postMessage()">发布留言</button></div>
</div>
</div>
<div id="msgLoginPrompt" style="display:none;text-align:center;padding:40px 20px;background:#fff;border:1px solid #e8ecf1;border-radius:16px;margin-bottom:20px">
<div style="font-size:40px;margin-bottom:12px">🔐</div>
<div style="font-size:15px;color:#666;margin-bottom:16px">请先登录后发表留言</div>
<button class="user-btn primary" onclick="openModal()">登录</button>
</div>

<div class="card">
<div class="card-title">💬 全部留言</div>
<div id="msgBoard"><div class="loading"><div class="spinner"></div>加载中...</div></div>
</div>

<script>
function postMessage(){
  const name=document.getElementById('msgName').value.trim()||'访客';
  const content=document.getElementById('msgContent').value.trim();
  if(!content){alert('请输入留言内容');return}
  fetch('api/index.php?source=msg_add',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:name,content:content})})
    .then(r=>r.json()).then(d=>{
      if(d.success){document.getElementById('msgContent').value='';loadMessages()}
      else alert(d.error||'发布失败')
    }).catch(()=>alert('网络错误'))
}
function loadMessages(){
  const el=document.getElementById('msgBoard');
  fetch('api/index.php?source=msg_list').then(r=>r.json()).then(d=>{
    if(!d.success||!d.data.length){el.innerHTML='<div style="text-align:center;padding:40px;color:#aaa;font-size:14px">暂无留言</div>';return}
    el.innerHTML=d.data.map(m=>
      '<div style="padding:16px 0;border-bottom:1px solid #f0f2f5">'+
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">'+
      '<span style="font-weight:600;font-size:14px">'+esc(m.name||'匿名')+'</span>'+
      '<span style="font-size:12px;color:#999">'+m.created_at+'</span></div>'+
      '<div style="font-size:14px;color:#555;line-height:1.6;white-space:pre-wrap">'+esc(m.content)+'</div></div>'
    ).join('')
  }).catch(()=>{el.innerHTML='<div class=error>加载失败</div>'})
}
function checkMsgUser(){var ok=g_user&&g_user.username;document.getElementById('msgPostArea').style.display=ok?'':'none';document.getElementById('msgLoginPrompt').style.display=ok?'none':'';if(ok)document.getElementById('msgName').value=g_user.username}
setTimeout(checkMsgUser,500);
loadMessages();
</script>
