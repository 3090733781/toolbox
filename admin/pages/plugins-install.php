<?php if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') return; ?>
<div class="card"><div class="card-title">📥 安装新插件</div>
<p style="font-size:13px;color:#888;margin-bottom:16px">上传 ZIP 包（需包含 plugin.json 清单文件）</p>
<div style="border:2px dashed #d0d5dd;border-radius:8px;padding:40px 20px;text-align:center;cursor:pointer;background:#fafbfc" onclick="document.getElementById('pluginFile').click()">
<div style="font-size:36px;margin-bottom:8px">📦</div><div style="font-size:14px;color:#888">点击选择插件包</div>
<input type="file" id="pluginFile" accept=".zip" style="display:none" onchange="installPlugin(this)"></div>
<div id="pluginInstallResult"></div></div>
<div class="card"><div class="card-title">📝 插件规范</div>
<p style="font-size:13px;color:#666;line-height:1.8">目录：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">plugins/插件名/plugin.json</code><br>
格式：<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px">{"name":"插件名","version":"1.0","description":"说明","author":"作者","type":"tool"}</code></p></div>
<script>
function installPlugin(i){var f=i.files[0];if(!f)return;var el=document.getElementById('pluginInstallResult');el.innerHTML='<div class=loading><div class=spinner></div>安装中...</div>';var fd=new FormData();fd.append('file',f);fetch('../api/index.php?source=plugin_install',{method:'POST',body:fd}).then(r=>r.json()).then(function(d){if(d.success){el.innerHTML='<div class=msg style=display:block>✅ 插件「'+esc(d.data.manifest?.name||d.data.name)+'」安装成功，正在跳转...</div>';setTimeout(function(){window.location.href='?p=plugins'},1500)}else el.innerHTML='<div class=err>'+esc(d.error)+'</div>'}).catch(function(){el.innerHTML='<div class=err>网络错误</div>'})}
</script>
