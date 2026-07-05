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
.dev-textarea{width:100%;min-height:160px;padding:12px 14px;border:2px solid var(--bd);border-radius:var(--rs);font-size:13px;font-family:monospace;outline:none;resize:vertical;transition:all .2s;background:#fafbfc}
.dev-textarea:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(79,106,245,.12);background:#fff}
.dev-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px}
.dev-row .dev-input{flex:1;margin-bottom:0}
.btn{padding:9px 22px;border-radius:var(--rs);font-size:13px;font-weight:600;cursor:pointer;border:none;background:linear-gradient(135deg,var(--p),var(--pd));color:#fff;transition:all .2s;box-shadow:0 2px 6px rgba(79,106,245,.2)}
.btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(79,106,245,.3)}
.btn:active{transform:translateY(0)}
.output-wrap{position:relative;margin-top:10px}
.dev-output{width:100%;min-height:80px;padding:14px 16px;border:2px solid var(--bd);border-radius:var(--rs);font-size:13px;font-family:monospace;background:#fafbfc;white-space:pre-wrap;word-break:break-all;border-left:4px solid var(--p);transition:all .2s;color:var(--t);line-height:1.6}
.dev-output:empty::before{content:attr(data-placeholder);color:#c0c4cc;font-style:italic;font-family:sans-serif}
.copy-btn{position:absolute;top:8px;right:8px;background:var(--cb);border:1px solid var(--bd);border-radius:6px;padding:3px 10px;font-size:12px;cursor:pointer;opacity:0;transition:all .2s;z-index:2;color:var(--ts);display:flex;align-items:center;gap:4px}
.output-wrap:hover .copy-btn{opacity:1}
.copy-btn:hover{background:var(--p);color:#fff;border-color:var(--p);transform:scale(1.05)}
.color-preview{height:80px;border-radius:var(--rs);border:2px solid var(--bd);transition:all .3s;margin-top:10px;display:flex;align-items:center;justify-content:center;font-size:13px;color:rgba(255,255,255,.8);font-weight:500;text-shadow:0 1px 3px rgba(0,0,0,.3)}
select.dev-input{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px;cursor:pointer}
@media(max-width:640px){.hero{padding:20px 16px}.hero h1{font-size:20px}.card{padding:16px}.tab-btn{padding:6px 12px;font-size:12px}}
</style>
<div class="hero"><h1>🧰 开发工具箱</h1><p>JSON格式化 / Base64 / URL编码 / 时间戳 / 颜色转换 / 正则测试 / 手机查询 / 身份证查询</p></div>
<div class="tab-bar" id="toolTabs">
<button class="tab-btn active" data-tool="json">📋 JSON</button>
<button class="tab-btn" data-tool="base64">🔐 Base64</button>
<button class="tab-btn" data-tool="url">🔗 URL编码</button>
<button class="tab-btn" data-tool="timestamp">⏰ 时间戳</button>
<button class="tab-btn" data-tool="color">🎨 颜色</button>
<button class="tab-btn" data-tool="uuid">🆔 UUID</button>
<button class="tab-btn" data-tool="regex">🔍 正则</button>
<button class="tab-btn" data-tool="phone">📱 手机号</button>
<button class="tab-btn" data-tool="idcard">🪪 身份证</button>
<button class="tab-btn" data-tool="calendar">🏮 农历</button>
<button class="tab-btn" data-tool="datecalc">📅 日期计算</button>
</div>
<div class="card"><div class="panel-inner">

<div class="tool-panel active" id="panel-json">
<div class="card-title">📋 JSON 格式化</div>
<textarea class="dev-textarea" id="jsonInput" placeholder='输入JSON，如：{"name":"test","age":18}'></textarea>
<div class="dev-row"><button class="btn" onclick="formatJSON()">✨ 格式化</button><button class="btn" onclick="compressJSON()">📦 压缩</button><button class="btn" onclick="validateJSON()">✅ 校验</button></div>
<div class="output-wrap"><div class="dev-output" id="jsonOutput" data-placeholder="格式化后的 JSON 将在这里显示..."></div><button class="copy-btn" data-target="jsonOutput" onclick="copyContent('jsonOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-base64">
<div class="card-title">🔐 Base64 编码/解码</div>
<textarea class="dev-textarea" id="b64Input" placeholder="输入文本"></textarea>
<div class="dev-row"><button class="btn" onclick="b64Encode()">🔒 编码</button><button class="btn" onclick="b64Decode()">🔓 解码</button></div>
<div class="output-wrap"><div class="dev-output" id="b64Output" data-placeholder="Base64 结果将在这里显示..."></div><button class="copy-btn" data-target="b64Output" onclick="copyContent('b64Output')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-url">
<div class="card-title">🔗 URL 编码/解码</div>
<textarea class="dev-textarea" id="urlInput" placeholder="输入文本或URL"></textarea>
<div class="dev-row"><button class="btn" onclick="urlEncode()">🔒 编码</button><button class="btn" onclick="urlDecode()">🔓 解码</button></div>
<div class="output-wrap"><div class="dev-output" id="urlOutput" data-placeholder="URL 编码结果将在这里显示..."></div><button class="copy-btn" data-target="urlOutput" onclick="copyContent('urlOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-timestamp">
<div class="card-title">⏰ 时间戳转换</div>
<div class="dev-row"><input class="dev-input" id="tsInput" placeholder="输入时间戳（秒或毫秒）" style="margin:0"><button class="btn" onclick="tsToDate()">→ 转日期</button></div>
<div class="output-wrap"><div class="dev-output" id="tsOutput" data-placeholder="转换后的日期将在这里显示..."></div><button class="copy-btn" data-target="tsOutput" onclick="copyContent('tsOutput')">📋 复制</button></div>
<div style="margin-top:14px"><div class="dev-row"><input class="dev-input" id="tsDateInput" type="datetime-local" style="margin:0"><button class="btn" onclick="dateToTs()">→ 转时间戳</button></div></div>
<div class="output-wrap"><div class="dev-output" id="tsOutput2" data-placeholder="转换后的时间戳将在这里显示..."></div><button class="copy-btn" data-target="tsOutput2" onclick="copyContent('tsOutput2')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-color">
<div class="card-title">🎨 颜色代码转换</div>
<div class="dev-row"><input class="dev-input" id="colorInput" placeholder="输入颜色值，如 #ff0000 / rgb(255,0,0) / hsl(0,100%,50%)" style="margin:0"><button class="btn" onclick="convertColor()">🎨 转换</button></div>
<div class="output-wrap"><div class="dev-output" id="colorOutput" data-placeholder="颜色转换结果将在这里显示..."></div><button class="copy-btn" data-target="colorOutput" onclick="copyContent('colorOutput')">📋 复制</button></div>
<div class="color-preview" id="colorPreview"></div>
</div>

<div class="tool-panel" id="panel-uuid">
<div class="card-title">🎲 UUID 生成</div>
<div class="dev-row"><button class="btn" onclick="genUUID()">🎲 生成一个</button><button class="btn" onclick="genUUIDs()">📋 批量生成 5 个</button></div>
<div class="output-wrap"><div class="dev-output" id="uuidOutput" data-placeholder="点击按钮生成 UUID..."></div><button class="copy-btn" data-target="uuidOutput" onclick="copyContent('uuidOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-regex">
<div class="card-title">🔍 正则表达式测试</div>
<div class="dev-row"><input class="dev-input" id="regexPattern" placeholder="正则表达式，如 \d+" style="margin:0"><input class="dev-input" id="regexFlags" placeholder="修饰符" value="gi" style="max-width:100px;margin:0"><button class="btn" onclick="testRegex()">🔍 测试</button></div>
<textarea class="dev-textarea" id="regexInput" placeholder="输入测试文本"></textarea>
<div class="output-wrap"><div class="dev-output" id="regexOutput" data-placeholder="匹配结果将在这里显示..."></div><button class="copy-btn" data-target="regexOutput" onclick="copyContent('regexOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-phone">
<div class="card-title">📱 手机号归属地查询</div>
<div class="dev-row"><input class="dev-input" id="phoneInput" placeholder="输入手机号" style="margin:0"><button class="btn" onclick="queryPhone()">🔍 查询</button></div>
<div class="output-wrap"><div class="dev-output" id="phoneOutput" data-placeholder="查询结果将在这里显示..."></div><button class="copy-btn" data-target="phoneOutput" onclick="copyContent('phoneOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-idcard">
<div class="card-title">🪪 身份证信息查询</div>
<div class="dev-row"><input class="dev-input" id="idcardInput" placeholder="输入18位身份证号" style="margin:0"><button class="btn" onclick="queryIDCard()">🔍 查询</button></div>
<div class="output-wrap"><div class="dev-output" id="idcardOutput" data-placeholder="查询结果将在这里显示..."></div><button class="copy-btn" data-target="idcardOutput" onclick="copyContent('idcardOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-calendar">
<div class="card-title">🏮 农历日期查询</div>
<div class="dev-row"><input class="dev-input" id="calendarDate" type="date" style="max-width:200px;margin:0"><button class="btn" onclick="queryLunar()">🏮 查询农历</button></div>
<div class="output-wrap"><div class="dev-output" id="calendarOutput" data-placeholder="农历信息将在这里显示..."></div><button class="copy-btn" data-target="calendarOutput" onclick="copyContent('calendarOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-datecalc">
<div class="card-title">📅 日期计算器</div>
<div class="dev-row"><input class="dev-input" id="dcDate1" type="date" style="max-width:200px;margin:0"><input class="dev-input" id="dcDate2" type="date" style="max-width:200px;margin:0"><button class="btn" onclick="dateDiff()">📏 计算相差</button></div>
<div class="output-wrap"><div class="dev-output" id="dcOutput" data-placeholder="日期差结果将在这里显示..."></div><button class="copy-btn" data-target="dcOutput" onclick="copyContent('dcOutput')">📋 复制</button></div>
<div style="margin-top:14px"><div class="dev-row"><input class="dev-input" id="dcDate3" type="date" style="max-width:200px;margin:0"><input class="dev-input" id="dcDays" placeholder="天数（正数=往后，负数=往前）" style="max-width:120px;margin:0"><button class="btn" onclick="dateAdd()">📅 推算日期</button></div></div>
<div class="output-wrap"><div class="dev-output" id="dcOutput2" data-placeholder="推算结果将在这里显示..."></div><button class="copy-btn" data-target="dcOutput2" onclick="copyContent('dcOutput2')">📋 复制</button></div>
</div>

</div></div>

<script>
function copyContent(id){var el=document.getElementById(id);var text=el.textContent;if(!text)return;navigator.clipboard.writeText(text).then(function(){var btn=document.querySelector('.copy-btn[data-target="'+id+'"]');btn.textContent='✓ 已复制';setTimeout(function(){btn.textContent='📋 复制'},2000)})}
var tabs=document.querySelectorAll('.tab-btn');tabs.forEach(function(t){t.addEventListener('click',function(){tabs.forEach(function(x){x.classList.remove('active')});document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('active')});this.classList.add('active');document.getElementById('panel-'+this.dataset.tool).classList.add('active')})});

function formatJSON(){var v=document.getElementById('jsonInput').value.trim();try{var o=JSON.parse(v);document.getElementById('jsonOutput').textContent=JSON.stringify(o,null,2)}catch(e){document.getElementById('jsonOutput').textContent='JSON 解析错误: '+e.message}}
function compressJSON(){var v=document.getElementById('jsonInput').value.trim();try{var o=JSON.parse(v);document.getElementById('jsonOutput').textContent=JSON.stringify(o)}catch(e){document.getElementById('jsonOutput').textContent='JSON 解析错误: '+e.message}}
function validateJSON(){var v=document.getElementById('jsonInput').value.trim();try{JSON.parse(v);document.getElementById('jsonOutput').textContent='✅ 有效的 JSON'}catch(e){document.getElementById('jsonOutput').textContent='❌ JSON 格式错误: '+e.message}}

function b64Encode(){var v=document.getElementById('b64Input').value;document.getElementById('b64Output').textContent=btoa(unescape(encodeURIComponent(v)))}
function b64Decode(){var v=document.getElementById('b64Input').value;try{document.getElementById('b64Output').textContent=decodeURIComponent(escape(atob(v)))}catch(e){document.getElementById('b64Output').textContent='解码失败: '+e.message}}

function urlEncode(){document.getElementById('urlOutput').textContent=encodeURIComponent(document.getElementById('urlInput').value)}
function urlDecode(){try{document.getElementById('urlOutput').textContent=decodeURIComponent(document.getElementById('urlInput').value)}catch(e){document.getElementById('urlOutput').textContent='解码失败: '+e.message}}

function tsToDate(){var v=document.getElementById('tsInput').value.trim();if(!v)return;var n=parseInt(v);if(v.length>10)n=Math.floor(n/1000);var d=new Date(n*1000);document.getElementById('tsOutput').textContent=d.toLocaleString('zh-CN',{timeZone:'Asia/Shanghai'})+' (北京时间)'}
function dateToTs(){var v=document.getElementById('tsDateInput').value;if(!v)return;var d=new Date(v);document.getElementById('tsOutput2').textContent='秒: '+Math.floor(d.getTime()/1000)+'\n毫秒: '+d.getTime()}

function convertColor(){var v=document.getElementById('colorInput').value.trim();var el=document.getElementById('colorOutput');var prev=document.getElementById('colorPreview');if(v.startsWith('#')){var h=v.slice(1);if(h.length===3)h=h.split('').map(function(c){return c+c}).join('');var r=parseInt(h.slice(0,2),16),g=parseInt(h.slice(2,4),16),b=parseInt(h.slice(4,6),16);if(!isNaN(r)){el.textContent='HEX: '+v+'\nRGB: rgb('+r+','+g+','+b+')\nHSL: hsl('+rgbToHsl(r,g,b)+')';prev.style.background=v;return}}var m=v.match(/rgb\((\d+),(\d+),(\d+)\)/);if(m){var r=parseInt(m[1]),g=parseInt(m[2]),b=parseInt(m[3]);var h='#'+[r,g,b].map(function(x){return('0'+x.toString(16)).slice(-2)}).join('');el.textContent='RGB: rgb('+r+','+g+','+b+')\nHEX: '+h+'\nHSL: hsl('+rgbToHsl(r,g,b)+')';prev.style.background='rgb('+r+','+g+','+b+')';return}m=v.match(/hsl\(([\d.]+),([\d.]+)%,([\d.]+)%\)/);if(m){var rgb=hslToRgb(parseFloat(m[1]),parseFloat(m[2])/100,parseFloat(m[3])/100);var h='#'+rgb.map(function(x){return('0'+x.toString(16)).slice(-2)}).join('');el.textContent='HSL: '+v+'\nHEX: '+h+'\nRGB: rgb('+rgb.join(',')+')';prev.style.background='rgb('+rgb.join(',')+')';return}el.textContent='无法识别的颜色格式';prev.style.background=''}
function rgbToHsl(r,g,b){r/=255;g/=255;b/=255;var mx=Math.max(r,g,b),mn=Math.min(r,g,b),h=0,s=0,l=(mx+mn)/2;if(mx!==mn){var d=mx-mn;s=l>0.5?d/(2-mx-mn):d/(mx+mn);switch(mx){case r:h=((g-b)/d+(g<b?6:0))/6;break;case g:h=((b-r)/d+2)/6;break;case b:h=((r-g)/d+4)/6;break}}return Math.round(h*360)+','+Math.round(s*100)+'%,'+Math.round(l*100)+'%'}
function hslToRgb(h,s,l){h/=360;var r,g,b;if(s===0){r=g=b=l}else{var hue2rgb=function(p,q,t){if(t<0)t+=1;if(t>1)t-=1;if(t<1/6)return p+(q-p)*6*t;if(t<1/2)return q;if(t<2/3)return p+(q-p)*(2/3-t)*6;return p};var q=l<0.5?l*(1+s):l+s-l*s,p=2*l-q;r=hue2rgb(p,q,h+1/3);g=hue2rgb(p,q,h);b=hue2rgb(p,q,h-1/3)}return[Math.round(r*255),Math.round(g*255),Math.round(b*255)]}

function genUUID(){document.getElementById('uuidOutput').textContent=([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,function(c){return(c^crypto.getRandomValues(new Uint8Array(1))[0]&15>>c/4).toString(16)})}
function genUUIDs(){var o='';for(var i=0;i<5;i++)o+=([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,function(c){return(c^crypto.getRandomValues(new Uint8Array(1))[0]&15>>c/4).toString(16)})+'\n';document.getElementById('uuidOutput').textContent=o}

function testRegex(){var p=document.getElementById('regexPattern').value;var f=document.getElementById('regexFlags').value;var t=document.getElementById('regexInput').value;try{var r=new RegExp(p,f);var m=t.match(r);if(m){document.getElementById('regexOutput').textContent='匹配 '+m.length+' 处:\n'+m.join('\n')}else{document.getElementById('regexOutput').textContent='无匹配'}}catch(e){document.getElementById('regexOutput').textContent='正则错误: '+e.message}}

function queryPhone(){var v=document.getElementById('phoneInput').value.trim();if(!v)return;var el=document.getElementById('phoneOutput');el.textContent='查询中...';fetch('api/index.php?source=dev_phone&query='+encodeURIComponent(v)).then(function(r){return r.json()}).then(function(d){if(d.success){el.textContent='📱 手机号: '+d.data.phone+'\n📍 归属地: '+d.data.province+' '+d.data.city+'\n🏢 运营商: '+d.data.isp}else{el.textContent='❌ '+d.error}}).catch(function(){el.textContent='❌ 查询失败'})}

function queryIDCard(){var v=document.getElementById('idcardInput').value.trim();if(!v)return;var el=document.getElementById('idcardOutput');el.textContent='查询中...';fetch('api/index.php?source=dev_idcard&query='+encodeURIComponent(v)).then(function(r){return r.json()}).then(function(d){if(d.success){el.textContent='🪪 身份证: '+d.data.id+'\n📍 出生地: '+d.data.province+' '+d.data.city+' '+d.data.district+'\n🎂 生日: '+d.data.birthday+'\n👤 性别: '+d.data.gender}else{el.textContent='❌ '+d.error}}).catch(function(){el.textContent='❌ 查询失败'})}

function queryLunar(){var v=document.getElementById('calendarDate').value;if(!v)return;var d=new Date(v);var lunar=['初一','初二','初三','初四','初五','初六','初七','初八','初九','初十','十一','十二','十三','十四','十五','十六','十七','十八','十九','二十','廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'];var gan=['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'];var zhi=['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'];var sx=['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];var m=d.getMonth()+1;var day=d.getDate();document.getElementById('calendarOutput').textContent='📅 公历: '+v+'\n🏮 农历: 暂用简算法 (月:'+m+' 日:'+day+')\n🐾 生肖: '+sx[(d.getFullYear()-4)%12]}

function dateDiff(){var v1=document.getElementById('dcDate1').value;var v2=document.getElementById('dcDate2').value;if(!v1||!v2)return;var d1=new Date(v1),d2=new Date(v2);var diff=Math.abs((d2-d1)/86400000);document.getElementById('dcOutput').textContent='📅 '+v1+' 至 '+v2+'\n⏳ 相差 '+diff+' 天'}
function dateAdd(){var v=document.getElementById('dcDate3').value;var n=parseInt(document.getElementById('dcDays').value);if(!v||isNaN(n))return;var d=new Date(v);d.setDate(d.getDate()+n);var y=d.getFullYear(),mo=String(d.getMonth()+1).padStart(2,'0'),da=String(d.getDate()).padStart(2,'0');document.getElementById('dcOutput2').textContent='📅 '+v+' + '+n+' 天 = '+y+'-'+mo+'-'+da}
</script>
