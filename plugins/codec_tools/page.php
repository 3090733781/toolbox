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
select.dev-input{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px;cursor:pointer}
@media(max-width:640px){.hero{padding:20px 16px}.hero h1{font-size:20px}.card{padding:16px}.tab-btn{padding:6px 12px;font-size:12px}}
</style>
<div class="hero"><h1>🔐 编码解码工具箱</h1><p>摩斯电码 / 凯撒密码 / ROT13 / 栅栏密码 / 培根密码 / Brainfuck / HTML实体编码 / Unicode编码</p></div>
<div class="tab-bar" id="toolTabs">
<button class="tab-btn active" data-tool="morse">📡 摩斯电码</button>
<button class="tab-btn" data-tool="caesar">🔑 凯撒密码</button>
<button class="tab-btn" data-tool="rot13">🔄 ROT13</button>
<button class="tab-btn" data-tool="railfence">🏰 栅栏密码</button>
<button class="tab-btn" data-tool="bacon">🥓 培根密码</button>
<button class="tab-btn" data-tool="brainfuck">🧠 Brainfuck</button>
<button class="tab-btn" data-tool="htmlentity">🔤 HTML实体</button>
<button class="tab-btn" data-tool="unicode">🌐 Unicode</button>
</div>
<div class="card"><div class="panel-inner">

<div class="tool-panel active" id="panel-morse">
<div class="card-title">📡 摩斯电码 编码/解码</div>
<textarea class="dev-textarea" id="morseInput" placeholder="输入文本或摩斯电码（空格分隔）"></textarea>
<div class="dev-row"><button class="btn" onclick="morseEncode()">📡 编码为摩斯码</button><button class="btn" onclick="morseDecode()">📖 解码为文本</button></div>
<div class="output-wrap"><div class="dev-output" id="morseOutput" data-placeholder="摩斯电码结果将在这里显示..."></div><button class="copy-btn" data-target="morseOutput" onclick="copyContent('morseOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-caesar">
<div class="card-title">🔑 凯撒密码</div>
<div class="dev-row"><input class="dev-input" id="caesarInput" placeholder="输入文本" style="margin:0"><input class="dev-input" id="caesarShift" placeholder="偏移量" value="3" style="max-width:100px;margin:0"><button class="btn" onclick="caesarEncode()">🔒 加密</button><button class="btn" onclick="caesarDecode()">🔓 解密</button></div>
<div class="output-wrap"><div class="dev-output" id="caesarOutput" data-placeholder="凯撒密码结果将在这里显示..."></div><button class="copy-btn" data-target="caesarOutput" onclick="copyContent('caesarOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-rot13">
<div class="card-title">🔄 ROT13</div>
<textarea class="dev-textarea" id="rot13Input" placeholder="输入文本"></textarea>
<div class="dev-row"><button class="btn" onclick="rot13Exec()">🔄 ROT13 转换</button></div>
<div class="output-wrap"><div class="dev-output" id="rot13Output" data-placeholder="ROT13 结果将在这里显示..."></div><button class="copy-btn" data-target="rot13Output" onclick="copyContent('rot13Output')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-railfence">
<div class="card-title">🏰 栅栏密码</div>
<div class="dev-row"><input class="dev-input" id="railfenceInput" placeholder="输入文本" style="margin:0"><input class="dev-input" id="railfenceRails" placeholder="栏数" value="3" style="max-width:100px;margin:0"><button class="btn" onclick="railfenceEncode()">🔒 加密</button><button class="btn" onclick="railfenceDecode()">🔓 解密</button></div>
<div class="output-wrap"><div class="dev-output" id="railfenceOutput" data-placeholder="栅栏密码结果将在这里显示..."></div><button class="copy-btn" data-target="railfenceOutput" onclick="copyContent('railfenceOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-bacon">
<div class="card-title">🥓 培根密码</div>
<textarea class="dev-textarea" id="baconInput" placeholder="输入文本（仅支持A-Z）"></textarea>
<div class="dev-row"><button class="btn" onclick="baconEncode()">🥓 编码</button><button class="btn" onclick="baconDecode()">🔍 解码</button></div>
<div class="output-wrap"><div class="dev-output" id="baconOutput" data-placeholder="培根密码结果将在这里显示..."></div><button class="copy-btn" data-target="baconOutput" onclick="copyContent('baconOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-brainfuck">
<div class="card-title">🧠 Brainfuck 解释器</div>
<div class="dev-row"><textarea class="dev-textarea" id="bfCode" placeholder="输入Brainfuck代码，如 ++++++++[>++++[>++>+++>+++>+<<<<-]>+>+>->>+[<]<-]>>.>---.+++++++..+++.>>.<-.<.+++.------.--------.>>+.>++." style="min-height:100px"></textarea></div>
<textarea class="dev-textarea" id="bfInput" placeholder="输入（可选）" style="min-height:60px"></textarea>
<div class="dev-row"><button class="btn" onclick="brainfuckRun()">▶️ 执行</button></div>
<div class="output-wrap"><div class="dev-output" id="bfOutput" data-placeholder="Brainfuck 执行结果将在这里显示..."></div><button class="copy-btn" data-target="bfOutput" onclick="copyContent('bfOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-htmlentity">
<div class="card-title">🔤 HTML实体编码/解码</div>
<textarea class="dev-textarea" id="htmlInput" placeholder="输入文本或HTML实体代码"></textarea>
<div class="dev-row"><button class="btn" onclick="htmlEncode()">🔒 编码</button><button class="btn" onclick="htmlDecode()">🔓 解码</button></div>
<div class="output-wrap"><div class="dev-output" id="htmlOutput" data-placeholder="HTML 实体结果将在这里显示..."></div><button class="copy-btn" data-target="htmlOutput" onclick="copyContent('htmlOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-unicode">
<div class="card-title">🌐 Unicode 编码/解码</div>
<textarea class="dev-textarea" id="unicodeInput" placeholder="输入文本或 \uXXXX 编码"></textarea>
<div class="dev-row"><button class="btn" onclick="unicodeEncode()">🔒 编码为 \uXXXX</button><button class="btn" onclick="unicodeDecode()">🔓 解码为文本</button></div>
<div class="output-wrap"><div class="dev-output" id="unicodeOutput" data-placeholder="Unicode 结果将在这里显示..."></div><button class="copy-btn" data-target="unicodeOutput" onclick="copyContent('unicodeOutput')">📋 复制</button></div>
</div>

</div></div>

<script>
function copyContent(id){var el=document.getElementById(id);var text=el.textContent;if(!text)return;navigator.clipboard.writeText(text).then(function(){var btn=document.querySelector('.copy-btn[data-target="'+id+'"]');btn.textContent='✓ 已复制';setTimeout(function(){btn.textContent='📋 复制'},2000)})}
var tabs=document.querySelectorAll('.tab-btn');tabs.forEach(function(t){t.addEventListener('click',function(){tabs.forEach(function(x){x.classList.remove('active')});document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('active')});this.classList.add('active');document.getElementById('panel-'+this.dataset.tool).classList.add('active')})});

var morseMap={A:'.-',B:'-...',C:'-.-.',D:'-..',E:'.',F:'..-.',G:'--.',H:'....',I:'..',J:'.---',K:'-.-',L:'.-..',M:'--',N:'-.',O:'---',P:'.--.',Q:'--.-',R:'.-.',S:'...',T:'-',U:'..-',V:'...-',W:'.--',X:'-..-',Y:'-.--',Z:'--..',0:'-----',1:'.----',2:'..---',3:'...--',4:'....-',5:'.....',6:'-....',7:'--...',8:'---..',9:'----.'};
var morseRev={};for(var k in morseMap)morseRev[morseMap[k]]=k;

function morseEncode(){var v=document.getElementById('morseInput').value.toUpperCase().trim();var r='';for(var i=0;i<v.length;i++){var c=v[i];if(c===' '){r+='  ';continue}if(morseMap[c]){r+=morseMap[c]+' '}else{r+=c+' '}}document.getElementById('morseOutput').textContent=r.trim()}

function morseDecode(){var v=document.getElementById('morseInput').value.trim();var words=v.split(/\s{2,}/);var r='';for(var w=0;w<words.length;w++){var letters=words[w].split(' ');for(var l=0;l<letters.length;l++){var c=letters[l];if(morseRev[c]){r+=morseRev[c]}else{r+=c}}if(w<words.length-1)r+=' '}document.getElementById('morseOutput').textContent=r}

function caesarShift(text,shift,decrypt){var s=decrypt?-shift:shift;var r='';for(var i=0;i<text.length;i++){var c=text[i];var code=c.charCodeAt(0);if(code>=65&&code<=90){r+=String.fromCharCode((code-65+s%26+26)%26+65)}else if(code>=97&&code<=122){r+=String.fromCharCode((code-97+s%26+26)%26+97)}else{r+=c}}return r}

function caesarEncode(){var v=document.getElementById('caesarInput').value;var n=parseInt(document.getElementById('caesarShift').value)||0;document.getElementById('caesarOutput').textContent=caesarShift(v,n,false)}
function caesarDecode(){var v=document.getElementById('caesarInput').value;var n=parseInt(document.getElementById('caesarShift').value)||0;document.getElementById('caesarOutput').textContent=caesarShift(v,n,true)}

function rot13Exec(){var v=document.getElementById('rot13Input').value;document.getElementById('rot13Output').textContent=caesarShift(v,13,false)}

function railfenceEncode(){var text=document.getElementById('railfenceInput').value;var rails=parseInt(document.getElementById('railfenceRails').value)||3;if(rails<2)rails=2;var fence=[];for(var i=0;i<rails;i++)fence[i]=[];var dir=1,row=0;for(var i=0;i<text.length;i++){fence[row].push(text[i]);row+=dir;if(row===rails-1||row===0)dir*=-1}var r='';for(var i=0;i<rails;i++)r+=fence[i].join('');document.getElementById('railfenceOutput').textContent=r}

function railfenceDecode(){var text=document.getElementById('railfenceInput').value;var rails=parseInt(document.getElementById('railfenceRails').value)||3;if(rails<2)rails=2;var cycleLen=2*(rails-1);var pos=[];for(var i=0;i<rails;i++)pos[i]=[];var idx=0;for(var r=0;r<rails;r++){var step1=2*(rails-1-r),step2=2*r;var cur=r;while(cur<text.length){pos[r][cur]=true;cur+=step1||cycleLen;if(cur<text.length&&step2){pos[r][cur]=true;cur+=step2||cycleLen}else if(!step2){cur+=cycleLen}}}var k=0;var out=[];for(var i=0;i<rails;i++)for(var j=0;j<text.length;j++)if(pos[i][j])out[j]=text[k++];document.getElementById('railfenceOutput').textContent=out.join('')}

var baconMap={};var baconLetters='ABCDEFGHIJKLMNOPQRSTUVWXYZ';for(var i=0;i<26;i++){var bin=i.toString(2).padStart(5,'0');baconMap[baconLetters[i]]=bin.replace(/0/g,'A').replace(/1/g,'B')}
var baconRev={};for(var k in baconMap)baconRev[baconMap[k]]=k;

function baconEncode(){var v=document.getElementById('baconInput').value.toUpperCase().replace(/[^A-Z]/g,'');var r='';for(var i=0;i<v.length;i++){var c=v[i];if(baconMap[c])r+=baconMap[c]+' '}document.getElementById('baconOutput').textContent=r.trim()}

function baconDecode(){var v=document.getElementById('baconInput').value.toUpperCase().replace(/[^AB]/g,'');var r='';for(var i=0;i<v.length;i+=5){var chunk=v.substr(i,5);if(chunk.length===5&&baconRev[chunk])r+=baconRev[chunk]}document.getElementById('baconOutput').textContent=r||'无法解码'}

function brainfuckRun(){var code=document.getElementById('bfCode').value;var input=document.getElementById('bfInput').value;var tape=new Array(30000).fill(0);var ptr=0,ip=0,out='',inp=0;var loop={};var stack=[];for(var i=0;i<code.length;i++){if(code[i]==='[')stack.push(i);if(code[i]===']'){var open=stack.pop();loop[open]=i;loop[i]=open}}try{while(ip<code.length){var c=code[ip];if(c==='>')ptr++;if(c==='<')ptr--;if(c==='+')tape[ptr]++;if(c==='-')tape[ptr]--;if(c==='.')out+=String.fromCharCode(tape[ptr]);if(c===','){tape[ptr]=inp<input.length?input.charCodeAt(inp++):0}if(c==='['&&tape[ptr]===0)ip=loop[ip];if(c===']'&&tape[ptr]!==0)ip=loop[ip];ip++}document.getElementById('bfOutput').textContent=out||'(无输出)'}catch(e){document.getElementById('bfOutput').textContent='执行错误: '+e.message}}

function htmlEncode(){var v=document.getElementById('htmlInput').value;var el=document.createElement('span');el.textContent=v;var r=el.innerHTML;document.getElementById('htmlOutput').textContent=r}

function htmlDecode(){var v=document.getElementById('htmlInput').value;var el=document.createElement('span');el.innerHTML=v;var r=el.textContent||el.innerText;document.getElementById('htmlOutput').textContent=r}

function unicodeEncode(){var v=document.getElementById('unicodeInput').value;var r='';for(var i=0;i<v.length;i++){var code=v.charCodeAt(i);r+='\\u'+code.toString(16).toUpperCase().padStart(4,'0')}document.getElementById('unicodeOutput').textContent=r}

function unicodeDecode(){var v=document.getElementById('unicodeInput').value;var r=v.replace(/\\u([0-9A-Fa-f]{4})/g,function(m,g){return String.fromCharCode(parseInt(g,16))});document.getElementById('unicodeOutput').textContent=r}
</script>
