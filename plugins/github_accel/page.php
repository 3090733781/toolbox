<style>
:root{--p:#4f6af5;--pd:#3d56e0;--pl:#e8ecff;--bg:#f0f2f6;--cb:#fff;--t:#1a1a2e;--ts:#6b7280;--bd:#e5e7eb;--sh:0 2px 8px rgba(0,0,0,.06);--shh:0 6px 20px rgba(79,106,245,.18);--r:12px;--rs:8px}
*{box-sizing:border-box;margin:0}
.hero{background:linear-gradient(135deg,#4f6af5,#7c5cfc);border-radius:16px;padding:28px 32px;margin-bottom:20px;color:#fff}
.hero h1{font-size:24px;font-weight:700;margin-bottom:6px}
.hero p{font-size:14px;opacity:.85;line-height:1.5}
.tab-bar{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;background:var(--cb);border:1px solid var(--bd);border-radius:var(--r);padding:10px 12px}
.tab-btn{padding:8px 16px;border-radius:20px;font-size:13px;font-weight:500;cursor:pointer;border:none;background:#f3f4f6;color:var(--ts);transition:all .2s ease;display:flex;align-items:center;gap:4px}
.tab-btn:hover{background:var(--pl);color:var(--p);transform:translateY(-1px)}
.tab-btn.active{background:linear-gradient(135deg,var(--p),var(--pd));color:#fff;box-shadow:0 2px 8px rgba(79,106,245,.3)}
.tool-panel{display:none;animation:fadeIn .25s ease}
.tool-panel.active{display:block}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.card{background:var(--cb);border-radius:var(--r);box-shadow:var(--sh);padding:24px 28px}
.panel-inner{border-top:3px solid var(--p);padding-top:18px}
.card-title{font-size:17px;font-weight:600;color:var(--t);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.dev-input{width:100%;padding:10px 14px;border:2px solid var(--bd);border-radius:var(--rs);font-size:14px;outline:none;font-family:monospace;margin-bottom:10px;transition:all .2s;background:#fafbfc}
.dev-input:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(79,106,245,.12);background:#fff}
.dev-textarea{width:100%;min-height:120px;padding:12px 14px;border:2px solid var(--bd);border-radius:var(--rs);font-size:13px;font-family:monospace;outline:none;resize:vertical;transition:all .2s;background:#fafbfc}
.dev-textarea:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(79,106,245,.12);background:#fff}
.dev-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px}
.dev-row .dev-input{flex:1;margin-bottom:0}
.btn{padding:9px 22px;border-radius:var(--rs);font-size:13px;font-weight:600;cursor:pointer;border:none;background:linear-gradient(135deg,var(--p),var(--pd));color:#fff;transition:all .2s;box-shadow:0 2px 6px rgba(79,106,245,.2)}
.btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(79,106,245,.3)}
.btn:active{transform:translateY(0)}
.btn-sm{padding:6px 14px;font-size:12px}
.btn-outline{background:transparent;color:var(--p);border:2px solid var(--p);box-shadow:none}
.btn-outline:hover{background:var(--pl);transform:translateY(-1px);box-shadow:none}
.output-wrap{position:relative;margin-top:10px}
.dev-output{width:100%;min-height:60px;padding:14px 16px;border:2px solid var(--bd);border-radius:var(--rs);font-size:13px;font-family:monospace;background:#fafbfc;white-space:pre-wrap;word-break:break-all;border-left:4px solid var(--p);transition:all .2s;color:var(--t);line-height:1.6}
.dev-output:empty::before{content:attr(data-placeholder);color:#c0c4cc;font-style:italic;font-family:sans-serif}
.copy-btn{position:absolute;top:8px;right:8px;background:var(--cb);border:1px solid var(--bd);border-radius:6px;padding:3px 10px;font-size:12px;cursor:pointer;opacity:0;transition:all .2s;z-index:2;color:var(--ts);display:flex;align-items:center;gap:4px}
.output-wrap:hover .copy-btn{opacity:1}
.copy-btn:hover{background:var(--p);color:#fff;border-color:var(--p);transform:scale(1.05)}
.link-list{display:flex;flex-direction:column;gap:8px}
.link-item{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#fafbfc;border:1px solid var(--bd);border-radius:var(--rs);transition:all .2s;gap:10px}
.link-item:hover{background:var(--pl);border-color:var(--p)}
.link-item .label{font-size:13px;font-weight:500;color:var(--t);white-space:nowrap;min-width:80px}
.link-item .url{font-size:12px;font-family:monospace;color:var(--ts);flex:1;word-break:break-all}
.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;margin-left:6px}
.tag-gh{background:#e8ecff;color:#4f6af5}
.tag-proxy{background:#e6f7e6;color:#22a63e}
.help-box{background:#fff9e6;border:1px solid #ffe08a;border-radius:var(--rs);padding:12px 16px;font-size:13px;color:#8a6d00;line-height:1.6;margin-bottom:14px}
.help-box strong{color:#6b4f00}
@media(max-width:640px){.hero{padding:20px 16px;border-radius:12px}.hero h1{font-size:20px}.hero p{font-size:13px}.card{padding:16px;border-radius:10px}.card-title{font-size:15px}.tab-bar{padding:8px;gap:4px;overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;scrollbar-width:none}.tab-bar::-webkit-scrollbar{display:none}.tab-btn{padding:7px 12px;font-size:12px;white-space:nowrap;flex-shrink:0}.dev-row{flex-direction:column;gap:8px}.dev-row .dev-input{margin-bottom:0}.dev-input,.dev-textarea{font-size:15px}.dev-output{font-size:12px;min-height:50px;padding:12px}.copy-btn{opacity:1;top:6px;right:6px;padding:4px 10px;font-size:11px}.link-item{flex-direction:column;align-items:stretch;gap:6px;padding:10px 12px}.link-item .label{font-size:12px}.link-item .url{font-size:11px}.link-item .btn{align-self:flex-end}.help-box{font-size:12px;padding:10px 12px}.btn{padding:10px 20px;font-size:13px}.btn-sm{padding:8px 14px;font-size:12px}.btn-outline:active{background:var(--pl);transform:scale(0.97)}.panel-inner{padding-top:12px}}
</style>
<div class="hero"><h1>⚡ GitHub 加速</h1><p>通过代理加速 GitHub 资源的访问和下载，支持仓库克隆、Release、Raw 文件等</p></div>
<div class="tab-bar" id="toolTabs">
<button class="tab-btn active" data-tool="convert">🔄 URL 转换</button>
<button class="tab-btn" data-tool="quick">📋 常用链接</button>
<button class="tab-btn" data-tool="help">❓ 使用说明</button>
</div>
<div class="card"><div class="panel-inner">

<div class="tool-panel active" id="panel-convert">
<div class="card-title">🔄 GitHub URL 转加速链接</div>
<div class="help-box"><strong>提示：</strong>输入 GitHub 原始链接，自动转换为加速链接。支持 <strong>github.com</strong>、<strong>raw.githubusercontent.com</strong>、<strong>github releases</strong> 等类型。</div>
<div class="dev-row"><input class="dev-input" id="ghUrlInput" placeholder="输入 GitHub 链接，如 https://github.com/xxx/xxx" style="margin:0"><button class="btn" onclick="convertUrl()">⚡ 转换</button></div>
<div class="output-wrap"><div class="dev-output" id="urlOutput" data-placeholder="转换后的加速链接将在这里显示..."></div><button class="copy-btn" data-target="urlOutput" onclick="copyContent('urlOutput')">📋 复制</button></div>
<div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
<button class="btn btn-sm btn-outline" onclick="fillExample('https://github.com/curl/curl/archive/refs/tags/curl-8_12_1.tar.gz')">📦 Release 示例</button>
<button class="btn btn-sm btn-outline" onclick="fillExample('https://raw.githubusercontent.com/curl/curl/master/README.md')">📄 Raw 示例</button>
<button class="btn btn-sm btn-outline" onclick="fillExample('https://github.com/curl/curl.git')">📂 Git Clone 示例</button>
</div>
</div>

<div class="tool-panel" id="panel-quick">
<div class="card-title">📋 常用加速链接</div>
<div class="help-box" style="margin-bottom:16px"><strong>使用方式：</strong>点击目标右侧的"复制"按钮，将加速链接复制到剪贴板，然后在终端或下载工具中使用。</div>
<div class="link-list" id="quickLinks"></div>
</div>

<div class="tool-panel" id="panel-help">
<div class="card-title">❓ 使用说明</div>
<div style="line-height:1.8;font-size:14px;color:var(--t)">
<p style="margin-bottom:12px"><strong>⚡ 加速原理</strong></p>
<p style="margin-bottom:16px;color:var(--ts)">本工具通过代理服务器 <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px">https://cf.098878.xyz/</code> 转发 GitHub 请求，解决国内网络环境下 GitHub 访问缓慢、下载失败的问题。</p>
<p style="margin-bottom:12px"><strong>📌 支持的加速类型</strong></p>
<ul style="margin-bottom:16px;padding-left:20px;color:var(--ts)">
<li><strong>Git Clone</strong> — 加速 git clone 仓库</li>
<li><strong>Release 下载</strong> — 加速 Release 资源下载</li>
<li><strong>Raw 文件</strong> — 加速 Raw 文件访问</li>
<li><strong>仓库页面</strong> — 加速 GitHub 网页访问</li>
</ul>
<p style="margin-bottom:12px"><strong>💡 使用方式</strong></p>
<ul style="margin-bottom:16px;padding-left:20px;color:var(--ts)">
<li>在 <strong>URL 转换</strong> 中输入任意 GitHub 链接，获取加速链接</li>
<li>在 <strong>常用链接</strong> 中直接复制常用加速链接</li>
<li>加速链接格式: <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px">https://cf.098878.xyz/原始链接</code></li>
</ul>
</div>
</div>

</div></div>

<script>
var PROXY_HOST='https://cf.098878.xyz/';

function copyContent(id){
var el=document.getElementById(id);
var text=el.textContent;
if(!text)return;
navigator.clipboard.writeText(text).then(function(){
var btn=document.querySelector('.copy-btn[data-target="'+id+'"]');
btn.textContent='✓ 已复制';
setTimeout(function(){btn.textContent='📋 复制'},2000)
})}

function copyText(text){
navigator.clipboard.writeText(text).then(function(){
var btn=event.target;
btn.textContent='✓ 已复制';
setTimeout(function(){btn.textContent='📋 复制'},2000)
})}

var tabs=document.querySelectorAll('.tab-btn');
tabs.forEach(function(t){t.addEventListener('click',function(){
tabs.forEach(function(x){x.classList.remove('active')});
document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('active')});
this.classList.add('active');
document.getElementById('panel-'+this.dataset.tool).classList.add('active')
})});

function buildProxyUrl(url){
url=url.trim();
if(!url)return '';
if(url.indexOf('://')===-1)url='https://'+url;
return PROXY_HOST+url;
}

function convertUrl(){
var input=document.getElementById('ghUrlInput').value.trim();
var out=document.getElementById('urlOutput');
if(!input){out.textContent='请输入 GitHub 链接';return}
if(input.indexOf('github.com')===-1&&input.indexOf('raw.githubusercontent.com')===-1){out.textContent='⚠️ 请提供有效的 GitHub 链接（需包含 github.com 或 raw.githubusercontent.com）';return}
var proxyUrl=buildProxyUrl(input);
var type='';
if(input.indexOf('.git')>-1)type='📂 Git Clone';
else if(input.indexOf('releases/download')>-1||input.indexOf('archive/refs')>-1)type='📦 Release';
else if(input.indexOf('raw.githubusercontent.com')>-1)type='📄 Raw 文件';
else type='🌐 网页';
out.textContent=type+' 加速链接:\n'+proxyUrl;
}

function fillExample(url){
document.getElementById('ghUrlInput').value=url;
convertUrl();
}

var quickData=[
{label:'GitHub 首页',url:'https://github.com',note:'加速访问 GitHub'},
{label:'GitHub 加速站',url:'https://cf.098878.xyz/https://github.com',note:'代理后的首页'},
{label:'Git Clone 模板',url:'https://cf.098878.xyz/https://github.com/用户/仓库.git',note:'替换用户/仓库'},
{label:'Release 下载模板',url:'https://cf.098878.xyz/https://github.com/用户/仓库/releases/download/v1.0.0/file.zip',note:'替换为实际路径'},
{label:'Raw 文件模板',url:'https://cf.098878.xyz/https://raw.githubusercontent.com/用户/仓库/分支/路径/文件',note:'替换为实际路径'},
];

function renderQuickLinks(){
var container=document.getElementById('quickLinks');
container.innerHTML='';
quickData.forEach(function(item){
var div=document.createElement('div');
div.className='link-item';
div.innerHTML='<span class="label">'+item.label+'</span><span class="url">'+item.url+'</span><button class="btn btn-sm btn-outline" onclick="copyText(\''+item.url.replace(/'/g,"\\'")+'\')">📋 复制</button>';
container.appendChild(div);
});
}
renderQuickLinks();
</script>
