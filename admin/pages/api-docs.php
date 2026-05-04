<?php if (empty($_SESSION['admin'])) return; ?>
<div class="card">
<div class="card-title">📖 API 接口文档</div>
<p style="font-size:13px;color:#888;margin-bottom:16px">工具箱对外 API 接口列表，供外部程序通过 API Key 调用</p>
<div id="apiDocsWrap"><div class="loading"><div class="spinner"></div>加载中...</div></div>
</div>
<script>
fetch('../api/index.php?source=api_docs').then(r=>r.json()).then(function(d){
  if(!d.success){document.getElementById('apiDocsWrap').innerHTML='<div class="err">'+esc(d.error)+'</div>';return}
  renderDocs(d.data);
}).catch(function(){document.getElementById('apiDocsWrap').innerHTML='<div class="err">加载失败</div>'});

function renderDocs(data){
  var base=data.base_url;
  var h='<div style="background:#f8f9fb;border:1px solid #e8ecf1;border-radius:8px;padding:14px;font-family:monospace;font-size:13px;text-align:center;color:#4f6af5;font-weight:600;margin-bottom:16px">'+esc(base)+'?source=xxx&key=密钥</div>';
  h+='<div style="margin-bottom:16px"><strong>认证方式：</strong> ';
  h+='<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px;font-size:12px">?key=密钥</code> ';
  h+='<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px;font-size:12px">X-API-Key: 密钥</code> ';
  h+='<code style="background:#f0f2ff;padding:2px 6px;border-radius:4px;font-size:12px">Authorization: Bearer 密钥</code></div>';
  data.docs.forEach(function(g){
    h+='<div style="margin-bottom:16px;border:1px solid #e8ecf1;border-radius:8px;overflow:hidden">';
    h+='<div style="padding:10px 14px;background:#f8f9fb;font-weight:700;font-size:14px;border-bottom:1px solid #e8ecf1">'+esc(g.icon)+' '+esc(g.group)+' <span style="font-weight:400;color:#888;font-size:12px">'+g.apis.length+' 个接口</span></div>';
    g.apis.forEach(function(a,i){
      h+='<div style="padding:10px 14px'+(i<g.apis.length-1?';border-bottom:1px solid #f0f2f5':'')+'">';
      h+='<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">';
      h+='<span style="font-family:monospace;font-weight:700;color:#4f6af5;font-size:14px">'+esc(a.source)+'</span>';
      if(a.method)h+='<span class="badge" style="background:#fff3e0;color:#e65100">'+a.method+'</span>';
      if(a.public)h+='<span class="badge" style="background:#f0fdf4;color:#16a34a">公开</span>';
      else h+='<span class="badge" style="background:#f0f2ff;color:#4f6af5">需认证</span>';
      h+='</div><div style="font-size:13px;color:#666">'+esc(a.desc)+'</div>';
      if(a.params&&a.params.length){
        h+='<div style="font-size:12px;color:#999;margin-top:4px">参数：'+a.params.map(function(p){return esc(p.name)+(p.required?'*':'')}).join(', ')+'</div>';
      }
      h+='<div style="margin-top:4px"><code style="background:#f8f9fb;padding:2px 8px;border-radius:4px;font-size:11px;color:#555">'+esc(base)+'?source='+esc(a.source)+(a.params.length?'&'+esc(a.params[0].name)+'=...':'')+'</code></div>';
      h+='</div>';
    });
    h+='</div>';
  });
  document.getElementById('apiDocsWrap').innerHTML=h;
}
function esc(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]})}
</script>
