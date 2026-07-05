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
<div class="hero"><h1>🔍 实用查询工具</h1><p>数字金额大写 / 区号邮编 / 世界各国查询 / 亲戚称呼计算</p></div>
<div class="tab-bar" id="toolTabs">
<button class="tab-btn active" data-tool="money">💴 金额大写</button>
<button class="tab-btn" data-tool="areacode">📞 区号邮编</button>
<button class="tab-btn" data-tool="country">🌍 世界各国</button>
<button class="tab-btn" data-tool="relative">👨‍👩‍👧‍👦 亲戚称呼</button>
</div>
<div class="card"><div class="panel-inner">

<div class="tool-panel active" id="panel-money">
<div class="card-title">💴 数字金额大写转换</div>
<div class="dev-row"><input class="dev-input" id="moneyInput" placeholder="输入金额数字，如 12345.67" style="margin:0"><button class="btn" onclick="moneyConvert()">💱 转换</button></div>
<div class="output-wrap"><div class="dev-output" id="moneyOutput" data-placeholder="金额大写结果将在这里显示..."></div><button class="copy-btn" data-target="moneyOutput" onclick="copyContent('moneyOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-areacode">
<div class="card-title">📞 区号邮编查询</div>
<div class="dev-row"><input class="dev-input" id="areaInput" placeholder="输入城市名，如 北京、上海" style="margin:0"><button class="btn" onclick="areaQuery()">🔍 查询</button></div>
<div class="output-wrap"><div class="dev-output" id="areaOutput" data-placeholder="区号邮编信息将在这里显示..."></div><button class="copy-btn" data-target="areaOutput" onclick="copyContent('areaOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-country">
<div class="card-title">🌍 世界各国查询</div>
<div class="dev-row"><input class="dev-input" id="countryInput" placeholder="输入国家名，如 中国、日本" style="margin:0"><button class="btn" onclick="countryQuery()">🔍 查询</button></div>
<div class="output-wrap"><div class="dev-output" id="countryOutput" data-placeholder="国家信息将在这里显示..."></div><button class="copy-btn" data-target="countryOutput" onclick="copyContent('countryOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-relative">
<div class="card-title">👨‍👩‍👧‍👦 亲戚称呼计算</div>
<select class="dev-input" id="relativeSelf" style="max-width:100%;margin-bottom:10px">
<option value="">请选择称呼关系</option>
<option value="爸爸的哥哥">爸爸的哥哥</option>
<option value="爸爸的弟弟">爸爸的弟弟</option>
<option value="爸爸的姐姐">爸爸的姐姐</option>
<option value="爸爸的妹妹">爸爸的妹妹</option>
<option value="妈妈的哥哥">妈妈的哥哥</option>
<option value="妈妈的弟弟">妈妈的弟弟</option>
<option value="妈妈的姐姐">妈妈的姐姐</option>
<option value="妈妈的妹妹">妈妈的妹妹</option>
<option value="爸爸的爸爸">爸爸的爸爸</option>
<option value="爸爸的妈妈">爸爸的妈妈</option>
<option value="妈妈的爸爸">妈妈的爸爸</option>
<option value="妈妈的妈妈">妈妈的妈妈</option>
<option value="哥哥">哥哥</option>
<option value="弟弟">弟弟</option>
<option value="姐姐">姐姐</option>
<option value="妹妹">妹妹</option>
<option value="爸爸的哥哥的儿子">爸爸的哥哥的儿子</option>
<option value="爸爸的弟弟的儿子">爸爸的弟弟的儿子</option>
<option value="爸爸的姐姐的儿子">爸爸的姐姐的儿子</option>
<option value="爸爸的妹妹的儿子">爸爸的妹妹的儿子</option>
<option value="妈妈的哥哥的儿子">妈妈的哥哥的儿子</option>
<option value="妈妈的弟弟的儿子">妈妈的弟弟的儿子</option>
<option value="妈妈的姐姐的儿子">妈妈的姐姐的儿子</option>
<option value="妈妈的妹妹的儿子">妈妈的妹妹的儿子</option>
<option value="爸爸的哥哥的女儿">爸爸的哥哥的女儿</option>
<option value="爸爸的弟弟的女儿">爸爸的弟弟的女儿</option>
<option value="爸爸的姐姐的女儿">爸爸的姐姐的女儿</option>
<option value="爸爸的妹妹的女儿">爸爸的妹妹的女儿</option>
<option value="妈妈的哥哥的女儿">妈妈的哥哥的女儿</option>
<option value="妈妈的弟弟的女儿">妈妈的弟弟的女儿</option>
<option value="妈妈的姐姐的女儿">妈妈的姐姐的女儿</option>
<option value="妈妈的妹妹的女儿">妈妈的妹妹的女儿</option>
</select>
<div class="dev-row"><button class="btn" onclick="relativeQuery()">🔍 查询称呼</button></div>
<div class="output-wrap"><div class="dev-output" id="relativeOutput" data-placeholder="称呼结果将在这里显示..."></div><button class="copy-btn" data-target="relativeOutput" onclick="copyContent('relativeOutput')">📋 复制</button></div>
</div>

</div></div>

<script>
function copyContent(id){var el=document.getElementById(id);var text=el.textContent;if(!text)return;navigator.clipboard.writeText(text).then(function(){var btn=document.querySelector('.copy-btn[data-target="'+id+'"]');btn.textContent='✓ 已复制';setTimeout(function(){btn.textContent='📋 复制'},2000)})}
var tabs=document.querySelectorAll('.tab-btn');tabs.forEach(function(t){t.addEventListener('click',function(){tabs.forEach(function(x){x.classList.remove('active')});document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('active')});this.classList.add('active');document.getElementById('panel-'+this.dataset.tool).classList.add('active')})});

var cnNum=['零','壹','贰','叁','肆','伍','陆','柒','捌','玖'];
var cnUnit=['','拾','佰','仟'];
var cnBigUnit=['','万','亿','万亿'];

function moneyConvert(){
var v=document.getElementById('moneyInput').value.trim();
if(!v||isNaN(parseFloat(v))){document.getElementById('moneyOutput').textContent='请输入有效数字';return}
var num=parseFloat(v);
if(num>9999999999999.99){document.getElementById('moneyOutput').textContent='金额过大，仅支持13位以内整数';return}
if(num===0){document.getElementById('moneyOutput').textContent='零元整';return}
var parts=v.split('.');
var intPart=parts[0].replace(/^0+/,'')||'0';
var decPart=parts.length>1?parts[1].slice(0,2):'';
var result='';
if(intPart!=='0'){
var intLen=intPart.length;
for(var i=0;i<intLen;i++){
var digit=parseInt(intPart[i]);
var unitIdx=(intLen-1-i)%4;
var bigIdx=Math.floor((intLen-1-i)/4);
if(digit===0){
if(result.slice(-1)!=='零'&&bigIdx>0&&unitIdx===0)result+=cnBigUnit[bigIdx];
else if(i<intLen-1&&parseInt(intPart[i+1])!==0)result+='零';
}else{
result+=cnNum[digit]+cnUnit[unitIdx];
if(unitIdx===0)result+=cnBigUnit[bigIdx];
}
}
if(result.slice(-1)==='零')result=result.slice(0,-1);
result+='元';
}
if(decPart.length===0){result+='整'}
else{
if(decPart[0]){result+=cnNum[parseInt(decPart[0])]+'角'}
if(decPart[1]){result+=cnNum[parseInt(decPart[1])]+'分'}
}
document.getElementById('moneyOutput').textContent=result;
}

var areaData={
'北京':{code:'010',zip:'100000'},
'上海':{code:'021',zip:'200000'},
'天津':{code:'022',zip:'300000'},
'重庆':{code:'023',zip:'400000'},
'广州':{code:'020',zip:'510000'},
'深圳':{code:'0755',zip:'518000'},
'杭州':{code:'0571',zip:'310000'},
'南京':{code:'025',zip:'210000'},
'武汉':{code:'027',zip:'430000'},
'成都':{code:'028',zip:'610000'},
'西安':{code:'029',zip:'710000'},
'长沙':{code:'0731',zip:'410000'},
'郑州':{code:'0371',zip:'450000'},
'沈阳':{code:'024',zip:'110000'},
'济南':{code:'0531',zip:'250000'},
'青岛':{code:'0532',zip:'266000'},
'苏州':{code:'0512',zip:'215000'},
'厦门':{code:'0592',zip:'361000'},
'大连':{code:'0411',zip:'116000'},
'昆明':{code:'0871',zip:'650000'},
'南宁':{code:'0771',zip:'530000'},
'合肥':{code:'0551',zip:'230000'},
'福州':{code:'0591',zip:'350000'},
'哈尔滨':{code:'0451',zip:'150000'},
'长春':{code:'0431',zip:'130000'},
'石家庄':{code:'0311',zip:'050000'},
'太原':{code:'0351',zip:'030000'},
'南昌':{code:'0791',zip:'330000'},
'贵阳':{code:'0851',zip:'550000'},
'海口':{code:'0898',zip:'570000'},
'兰州':{code:'0931',zip:'730000'},
'西宁':{code:'0971',zip:'810000'},
'呼和浩特':{code:'0471',zip:'010000'},
'乌鲁木齐':{code:'0991',zip:'830000'},
'拉萨':{code:'0891',zip:'850000'},
'东莞':{code:'0769',zip:'523000'},
'佛山':{code:'0757',zip:'528000'},
'温州':{code:'0577',zip:'325000'},
'宁波':{code:'0574',zip:'315000'},
'珠海':{code:'0756',zip:'519000'},
};

function areaQuery(){
var v=document.getElementById('areaInput').value.trim();
if(!v){document.getElementById('areaOutput').textContent='请输入城市名';return}
var data=areaData[v];
if(data){document.getElementById('areaOutput').textContent='📍 城市: '+v+'\n📞 区号: '+data.code+'\n📮 邮编: '+data.zip}
else{document.getElementById('areaOutput').textContent='未找到城市 "'+v+'" 的信息，请尝试输入完整城市名'}
}

var countryData={
'中国':{capital:'北京',lang:'中文',currency:'人民币 (CNY)'},
'日本':{capital:'东京',lang:'日语',currency:'日元 (JPY)'},
'韩国':{capital:'首尔',lang:'韩语',currency:'韩元 (KRW)'},
'美国':{capital:'华盛顿',lang:'英语',currency:'美元 (USD)'},
'英国':{capital:'伦敦',lang:'英语',currency:'英镑 (GBP)'},
'法国':{capital:'巴黎',lang:'法语',currency:'欧元 (EUR)'},
'德国':{capital:'柏林',lang:'德语',currency:'欧元 (EUR)'},
'意大利':{capital:'罗马',lang:'意大利语',currency:'欧元 (EUR)'},
'西班牙':{capital:'马德里',lang:'西班牙语',currency:'欧元 (EUR)'},
'俄罗斯':{capital:'莫斯科',lang:'俄语',currency:'卢布 (RUB)'},
'印度':{capital:'新德里',lang:'印地语、英语',currency:'卢比 (INR)'},
'加拿大':{capital:'渥太华',lang:'英语、法语',currency:'加元 (CAD)'},
'澳大利亚':{capital:'堪培拉',lang:'英语',currency:'澳元 (AUD)'},
'巴西':{capital:'巴西利亚',lang:'葡萄牙语',currency:'雷亚尔 (BRL)'},
'泰国':{capital:'曼谷',lang:'泰语',currency:'泰铢 (THB)'},
'越南':{capital:'河内',lang:'越南语',currency:'越南盾 (VND)'},
'新加坡':{capital:'新加坡',lang:'英语、中文、马来语、泰米尔语',currency:'新加坡元 (SGD)'},
'马来西亚':{capital:'吉隆坡',lang:'马来语',currency:'林吉特 (MYR)'},
'菲律宾':{capital:'马尼拉',lang:'菲律宾语、英语',currency:'比索 (PHP)'},
'印度尼西亚':{capital:'雅加达',lang:'印尼语',currency:'印尼盾 (IDR)'},
'埃及':{capital:'开罗',lang:'阿拉伯语',currency:'埃及镑 (EGP)'},
'南非':{capital:'比勒陀利亚',lang:'南非语、英语等',currency:'兰特 (ZAR)'},
'墨西哥':{capital:'墨西哥城',lang:'西班牙语',currency:'比索 (MXN)'},
'新西兰':{capital:'惠灵顿',lang:'英语、毛利语',currency:'新西兰元 (NZD)'},
};

function countryQuery(){
var v=document.getElementById('countryInput').value.trim();
if(!v){document.getElementById('countryOutput').textContent='请输入国家名';return}
var data=countryData[v];
if(data){document.getElementById('countryOutput').textContent='🌍 国家: '+v+'\n🏙️ 首都: '+data.capital+'\n🗣️ 语言: '+data.lang+'\n💰 货币: '+data.currency}
else{document.getElementById('countryOutput').textContent='未找到 "'+v+'" 的信息，请尝试输入中文国家全名'}
}

var relativeMap={
'爸爸的哥哥':'大伯 / 伯父',
'爸爸的弟弟':'叔叔 / 叔父',
'爸爸的姐姐':'姑妈 / 姑姑',
'爸爸的妹妹':'姑妈 / 姑姑',
'妈妈的哥哥':'舅舅 / 舅父',
'妈妈的弟弟':'舅舅 / 舅父',
'妈妈的姐姐':'姨妈 / 姨母',
'妈妈的妹妹':'姨妈 / 姨母',
'爸爸的爸爸':'爷爷 / 祖父',
'爸爸的妈妈':'奶奶 / 祖母',
'妈妈的爸爸':'外公 / 外祖父',
'妈妈的妈妈':'外婆 / 外祖母',
'哥哥':'哥哥 / 兄长',
'弟弟':'弟弟',
'姐姐':'姐姐',
'妹妹':'妹妹',
'爸爸的哥哥的儿子':'堂哥 / 堂弟',
'爸爸的弟弟的儿子':'堂哥 / 堂弟',
'爸爸的姐姐的儿子':'表哥 / 表弟',
'爸爸的妹妹的儿子':'表哥 / 表弟',
'妈妈的哥哥的儿子':'表哥 / 表弟',
'妈妈的弟弟的儿子':'表哥 / 表弟',
'妈妈的姐姐的儿子':'表哥 / 表弟',
'妈妈的妹妹的儿子':'表哥 / 表弟',
'爸爸的哥哥的女儿':'堂姐 / 堂妹',
'爸爸的弟弟的女儿':'堂姐 / 堂妹',
'爸爸的姐姐的女儿':'表姐 / 表妹',
'爸爸的妹妹的女儿':'表姐 / 表妹',
'妈妈的哥哥的女儿':'表姐 / 表妹',
'妈妈的弟弟的女儿':'表姐 / 表妹',
'妈妈的姐姐的女儿':'表姐 / 表妹',
'妈妈的妹妹的女儿':'表姐 / 表妹',
};

function relativeQuery(){
var v=document.getElementById('relativeSelf').value;
if(!v){document.getElementById('relativeOutput').textContent='请选择称呼关系';return}
var result=relativeMap[v];
if(result){document.getElementById('relativeOutput').textContent='👤 关系: '+v+'\n💬 称呼: '+result}
else{document.getElementById('relativeOutput').textContent='暂未收录该关系的称呼'}
}
</script>
