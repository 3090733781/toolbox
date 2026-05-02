<div class="hero"><h1>🏪 插件列表</h1><p>选择插件开始使用</p></div>
<style>
#searchWrap:focus-within{border-color:#4f6af5!important;box-shadow:0 4px 20px rgba(79,106,245,0.15)}
</style>
<div style="max-width:600px;margin:0 auto 24px">
<div style="display:flex;border:2px solid #e0e4ea;border-radius:12px;overflow:hidden;background:#fff;transition:border-color .2s" id="searchWrap">
<input type="text" id="pluginSearch" placeholder="搜索插件名称或关键词..." oninput="filterPlugins()" style="flex:1;padding:12px 16px;border:none;outline:none;font-size:14px;background:transparent;color:#333">
<span style="display:flex;align-items:center;padding:0 16px;color:#aaa;font-size:16px">🔍</span>
</div>
</div>
<div id="marketList"><div class="loading"><div class="spinner"></div>加载中...</div></div>
<script>
var g_plugins=[];
function loadPluginList(){fetch('api/index.php?source=plugin_list').then(r=>r.json()).then(function(d){var el=document.getElementById('marketList');if(!d.success||!d.data.length){el.innerHTML='<div style="text-align:center;padding:40px;color:#aaa">暂无插件</div>';return}g_plugins=d.data;renderPlugins('')}).catch(function(){})}
function renderPlugins(q){var el=document.getElementById('marketList');var f=q.toLowerCase();var filtered=g_plugins.filter(function(p){var m=p.manifest||{};var name=(m.name||p.name).toLowerCase();var desc=(m.description||'').toLowerCase();return name.indexOf(f)>=0||desc.indexOf(f)>=0});if(!filtered.length){el.innerHTML='<div style="text-align:center;padding:40px;color:#aaa">没有匹配的插件</div>';return}el.innerHTML='<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px">'+filtered.map(function(p){var m=p.manifest||{};var icon=m.icon||'🔌';var name=m.name||p.name;var desc=m.description||'';var url=p.has_page?'plugins/'+encodeURIComponent(p.name)+'/page.php':'';var click=url?'window.location.href=\''+url+'\'':'';return '<div style="background:#fff;border:1px solid #e8ecf1;border-radius:12px;padding:24px;cursor:'+(url?'pointer':'default')+';transition:all .15s" '+(url?'onclick="'+click+'"':'')+' onmouseover="this.style.borderColor=\'#4f6af5\';this.style.boxShadow=\'0 4px 20px rgba(79,106,245,.1)\'" onmouseout="this.style.borderColor=\'#e8ecf1\';this.style.boxShadow=\'none\'"><div style="font-size:36px;margin-bottom:10px">'+icon+'</div><div style="font-size:16px;font-weight:700;color:#333">'+esc(name)+'</div><div style="font-size:13px;color:#888;margin-top:6px;line-height:1.6">'+esc(desc)+'</div><div style="margin-top:12px">'+(url?'<span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#4f6af5;color:#fff;border-radius:6px;font-size:12px;font-weight:600">▶ 打开</span>':'<span style="display:inline-block;padding:4px 12px;background:#e8f5e9;color:#16a34a;border-radius:6px;font-size:12px;font-weight:600">✓ 已集成</span>')+'</div></div>'}).join('')+'</div>'}
function filterPlugins(){var q=document.getElementById('pluginSearch').value;renderPlugins(q)}
loadPluginList();
</script>
