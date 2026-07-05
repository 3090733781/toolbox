<?php if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') return; ?>
<div class="card"><div class="card-title">插件中心库</div>
<p style="font-size:13px;color:#888;margin-bottom:16px">从授权更新对接站获取插件，校验签名和 SHA256 后一键安装。</p>
<div id="pluginCenterList"><div class="loading"><div class="spinner"></div>正在读取插件中心...</div></div></div>

<div class="card"><div class="card-title">本地上传安装</div>
<p style="font-size:13px;color:#888;margin-bottom:16px">上传 ZIP 包（需包含 plugin.json 清单文件）</p>
<div style="border:2px dashed #d0d5dd;border-radius:8px;padding:40px 20px;text-align:center;cursor:pointer;background:#fafbfc" onclick="document.getElementById('pluginFile').click()">
<div style="font-size:36px;margin-bottom:8px">ZIP</div><div style="font-size:14px;color:#888">点击选择插件包</div>
<input type="file" id="pluginFile" accept=".zip" style="display:none" onchange="installLocalPlugin(this)"></div>
<div id="pluginInstallResult"></div></div>

<div class="card"><div class="card-title">插件规范</div>
<p style="font-size:13px;color:#666;line-height:1.8">目录：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">plugins/插件名/plugin.json</code><br>
格式：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">{"id":"plugin_id","name":"插件名","version":"1.0.0","description":"说明","author":"作者","type":"tool"}</code></p></div>

<script>
function fmtSize(n){n=Number(n||0);if(n>1048576)return(n/1048576).toFixed(2)+' MB';return(n/1024).toFixed(1)+' KB'}
function renderPluginCenter(list){var html='<table><thead><tr><th>插件</th><th>版本</th><th>说明</th><th>大小</th><th>发布时间</th><th>操作</th></tr></thead><tbody>';html+=list.map(function(p){var installed=!!p.installed;var action=installed?'<span class="badge badge-admin">已安装</span>':'<button class="btn btn-sm plugin-install-btn" data-id="'+esc(p.id||'')+'" data-name="'+esc(p.name||p.plugin_id||'')+'">一键安装</button>';return '<tr><td><strong>'+esc(p.name||p.plugin_id)+'</strong><br><span style="font-size:12px;color:#999">'+esc(p.plugin_id||'')+'</span></td><td>v'+esc(p.version||'?')+'</td><td style="color:#777;font-size:12px;max-width:300px">'+esc(p.description||p.changelog||'')+'</td><td>'+fmtSize(p.size)+'</td><td>'+esc(p.published_at||'')+'</td><td>'+action+'</td></tr>'}).join('');html+='</tbody></table>';return html}
function bindPluginButtons(){document.querySelectorAll('.plugin-install-btn').forEach(function(btn){btn.onclick=function(){installRemotePlugin(this.getAttribute('data-id'),this.getAttribute('data-name'))}})}
function loadPluginCenter(){var el=document.getElementById('pluginCenterList');el.innerHTML='<div class="loading"><div class="spinner"></div>正在读取插件中心...</div>';fetch('../api/index.php?source=plugin_center_list').then(r=>r.json()).then(function(d){if(!d.success){el.innerHTML='<div class=err>'+esc(d.error||'读取失败')+'</div>';return}if(!d.data.length){el.innerHTML='<div style="text-align:center;padding:28px;color:#aaa">插件中心暂无插件</div>';return}el.innerHTML=renderPluginCenter(d.data);bindPluginButtons()}).catch(function(){el.innerHTML='<div class=err>读取插件中心失败</div>'})}
function installRemotePlugin(id,name){if(!confirm('确认安装插件「'+name+'」？'))return;var el=document.getElementById('pluginCenterList');el.innerHTML='<div class="loading"><div class="spinner"></div>正在下载并安装插件...</div>';fetch('../api/index.php?source=plugin_center_install',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id})}).then(r=>r.json()).then(function(d){if(d.success){el.innerHTML='<div class=msg style=display:block>插件「'+esc(d.data.manifest?.name||d.data.name)+'」安装成功，正在跳转...</div>';setTimeout(function(){window.location.href='?p=plugins'},1200)}else{el.innerHTML='<div class=err>'+esc(d.error||'安装失败')+'</div><div style="margin-top:12px"><button class="btn btn-sm" onclick="loadPluginCenter()">重新加载</button></div>'}}).catch(function(){el.innerHTML='<div class=err>网络错误</div>'})}
function installLocalPlugin(i){var f=i.files[0];if(!f)return;var el=document.getElementById('pluginInstallResult');el.innerHTML='<div class=loading><div class=spinner></div>安装中...</div>';var fd=new FormData();fd.append('file',f);fetch('../api/index.php?source=plugin_install',{method:'POST',body:fd}).then(r=>r.json()).then(function(d){if(d.success){el.innerHTML='<div class=msg style=display:block>插件「'+esc(d.data.manifest?.name||d.data.name)+'」安装成功，正在跳转...</div>';setTimeout(function(){window.location.href='?p=plugins'},1200)}else el.innerHTML='<div class=err>'+esc(d.error||'安装失败')+'</div>'}).catch(function(){el.innerHTML='<div class=err>网络错误</div>'})}
loadPluginCenter();
</script>
