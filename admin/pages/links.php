<?php if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') return; ?>
<div class="card"><div class="card-title">🔗 友链管理</div>
<div style="display:flex;gap:8px;margin-bottom:16px"><input type="text" id="linkName" placeholder="网站名称" style="flex:1;padding:9px 12px;border:1px solid #d0d5dd;border-radius:6px;font-size:13px;outline:none"><input type="text" id="linkUrl" placeholder="https://" style="flex:1;padding:9px 12px;border:1px solid #d0d5dd;border-radius:6px;font-size:13px;outline:none"><button class="btn btn-primary" onclick="addLink()">添加</button></div>
<div id="linkList"><div class="loading">暂无友链</div></div></div>
<script>
function addLink(){var n=document.getElementById('linkName').value.trim();var u=document.getElementById('linkUrl').value.trim();if(!n||!u)return;var l=JSON.parse(localStorage.getItem('tb_links')||'[]');l.push({name:n,url:u,time:new Date().toLocaleString()});localStorage.setItem('tb_links',JSON.stringify(l));document.getElementById('linkName').value='';document.getElementById('linkUrl').value='';renderLinks()}
function renderLinks(){var el=document.getElementById('linkList');var l=JSON.parse(localStorage.getItem('tb_links')||'[]');if(!l.length){el.innerHTML='<div style="text-align:center;padding:20px;color:#aaa">暂无友链</div>';return}el.innerHTML='<table><thead><tr><th>名称</th><th>网址</th><th>时间</th><th>操作</th></tr></thead><tbody>'+l.map(function(x,i){return '<tr><td>'+esc(x.name)+'</td><td><a href="'+esc(x.url)+'" target=_blank>'+esc(x.url)+'</a></td><td>'+x.time+'</td><td><button class="btn btn-sm" onclick="deleteLink('+i+')">删除</button></td></tr>'}).join('')+'</tbody></table>'}
function deleteLink(i){var l=JSON.parse(localStorage.getItem('tb_links')||'[]');l.splice(i,1);localStorage.setItem('tb_links',JSON.stringify(l));renderLinks()}
renderLinks();
</script>
