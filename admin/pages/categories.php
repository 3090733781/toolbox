<?php if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') return; ?>
<div id="saveMsgCat" class="msg">✓ 保存成功</div>
<div class="card"><div class="card-title">📂 添加分类</div>
<div style="display:flex;gap:8px"><input type="text" id="catName" placeholder="分类名称" style="flex:1;padding:9px 12px;border:1px solid #d0d5dd;border-radius:6px;font-size:13px;outline:none">
<button class="btn btn-primary" onclick="addCat()">添加</button></div></div>

<div class="card"><div class="card-title">📋 分类列表</div>
<div style="margin-bottom:12px;display:flex;gap:8px;align-items:center">
<span style="font-size:13px;color:#555;font-weight:600">显示模式：</span>
<button class="btn-sm" id="modeListBtn" onclick="setDefaultMode('list')">📋 列表模式</button>
<button class="btn-sm" id="modeTopBtn" onclick="setDefaultMode('top')">📌 顶部模式</button>
</div>
<div id="catList"><div class="loading"><div class="spinner"></div>加载中...</div></div></div>

<div class="card"><div class="card-title">🔍 预览</div>
<div id="catPreview"><div class="loading">暂无分类</div></div></div>

<script>
function esc(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]})}

function addCat(){var n=document.getElementById('catName').value.trim();if(!n)return;fetch('../api/index.php?source=cat_add',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:n,mode:'list'})}).then(r=>r.json()).then(function(d){if(d.success){document.getElementById('catName').value='';loadCats()}else alert(d.error)}).catch(function(){alert('网络错误')})}

function loadCats(){fetch('../api/index.php?source=cat_list').then(r=>r.json()).then(function(d){var el=document.getElementById('catList');if(!d.success){el.innerHTML='<div class=err>'+esc(d.error)+'</div>';return}if(!d.data.length){el.innerHTML='<div style="text-align:center;padding:20px;color:#aaa">暂无分类</div>';renderPreview([]);return}el.innerHTML='<table><thead><tr><th style=width:40px>ID</th><th>名称</th><th>模式</th><th>时间</th><th>操作</th></tr></thead><tbody>'+d.data.map(function(c){return '<tr><td>'+c.id+'</td><td>'+esc(c.name)+'</td><td><span class="badge '+(c.mode==='top'?'badge-admin':'badge-user')+'" style="cursor:pointer" onclick="toggleMode('+c.id+',\''+c.mode+'\')">'+(c.mode==='top'?'📌 顶部':'📋 列表')+'</span></td><td>'+(c.created_at||'')+'</td><td><button class="btn btn-sm" onclick="if(confirm(\'确定删除？\')){fetch(\'../api/index.php?source=cat_delete&id='+c.id+'\').then(r=>r.json()).then(function(d2){if(d2.success)loadCats();else alert(d2.error)}).catch(function(){alert(\'网络错误\')})}">删除</button></td></tr>'}).join('')+'</tbody></table>';renderPreview(d.data)}).catch(function(){document.getElementById('catList').innerHTML='<div class=err>加载失败</div>'})}

function toggleMode(id,cur){var mode=cur==='top'?'list':'top';fetch('../api/index.php?source=cat_mode',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id,mode:mode})}).then(r=>r.json()).then(function(d){if(d.success)loadCats();else alert(d.error)}).catch(function(){alert('网络错误')})}

function setDefaultMode(mode){fetch('../api/index.php?source=cat_list?mode='+mode).then(function(){loadCats()})}

function renderPreview(cats){var el=document.getElementById('catPreview');if(!cats.length){el.innerHTML='<div style="text-align:center;padding:20px;color:#aaa">暂无分类</div>';return}var list=cats.filter(function(c){return c.mode==='list'});var top=cats.filter(function(c){return c.mode==='top'});var h='';if(top.length){h+='<div style="margin-bottom:12px"><strong style="font-size:13px;color:#555">📌 顶部模式：</strong><div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">'+top.map(function(c){return '<span style="padding:6px 14px;background:#4f6af5;color:#fff;border-radius:6px;font-size:13px">'+esc(c.name)+'</span>'}).join('')+'</div></div>'}if(list.length){h+='<div><strong style="font-size:13px;color:#555">📋 列表模式：</strong><div style="margin-top:6px">'+list.map(function(c){return '<div style="padding:8px 14px;border:1px solid #e8ecf1;border-radius:6px;margin-bottom:4px;font-size:13px">'+esc(c.name)+'</div>'}).join('')+'</div></div>'}el.innerHTML=h}

loadCats();
</script>
