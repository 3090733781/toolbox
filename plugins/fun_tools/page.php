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
<div class="hero"><h1>🎮 趣味小工具</h1><p>摸鱼日历 / 今天吃什么 / 土味情话 / 彩虹屁生成器 / 随机决策器</p></div>
<div class="tab-bar" id="toolTabs">
<button class="tab-btn active" data-tool="fish">🐟 摸鱼日历</button>
<button class="tab-btn" data-tool="food">🍽️ 今天吃什么</button>
<button class="tab-btn" data-tool="pickup">💕 土味情话</button>
<button class="tab-btn" data-tool="compliment">🌈 彩虹屁</button>
<button class="tab-btn" data-tool="decide">🎲 随机决策</button>
</div>
<div class="card"><div class="panel-inner">

<div class="tool-panel active" id="panel-fish">
<div class="card-title">🐟 摸鱼日历</div>
<div class="dev-row"><button class="btn" onclick="showFishCalendar()">📅 查看今天</button></div>
<div class="output-wrap"><div class="dev-output" id="fishOutput" data-placeholder="点击按钮查看今天的摸鱼日历..."></div><button class="copy-btn" data-target="fishOutput" onclick="copyContent('fishOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-food">
<div class="card-title">🍽️ 今天吃什么</div>
<div class="dev-row"><button class="btn" onclick="pickFood()">🍽️ 帮我决定!</button></div>
<div class="output-wrap"><div class="dev-output" id="foodOutput" data-placeholder="点击按钮让命运为你选择..."></div><button class="copy-btn" data-target="foodOutput" onclick="copyContent('foodOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-pickup">
<div class="card-title">💕 土味情话</div>
<div class="dev-row"><button class="btn" onclick="showPickup()">💕 来一句情话</button></div>
<div class="output-wrap"><div class="dev-output" id="pickupOutput" data-placeholder="点击按钮收获一句土味情话..."></div><button class="copy-btn" data-target="pickupOutput" onclick="copyContent('pickupOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-compliment">
<div class="card-title">🌈 彩虹屁生成器</div>
<div class="dev-row"><button class="btn" onclick="showCompliment()">🌟 夸夸我!</button></div>
<div class="output-wrap"><div class="dev-output" id="complimentOutput" data-placeholder="点击按钮收获赞美..."></div><button class="copy-btn" data-target="complimentOutput" onclick="copyContent('complimentOutput')">📋 复制</button></div>
</div>

<div class="tool-panel" id="panel-decide">
<div class="card-title">🎲 随机决策器</div>
<textarea class="dev-textarea" id="decideInput" placeholder="输入选项，每行一个&#10;如：&#10;吃火锅&#10;吃烧烤&#10;吃麻辣烫" style="min-height:160px"></textarea>
<div class="dev-row"><button class="btn" onclick="decidePick()">🎲 帮我决定!</button></div>
<div class="output-wrap"><div class="dev-output" id="decideOutput" data-placeholder="输入选项后点击按钮..."></div><button class="copy-btn" data-target="decideOutput" onclick="copyContent('decideOutput')">📋 复制</button></div>
</div>

</div></div>

<script>
function copyContent(id){var el=document.getElementById(id);var text=el.textContent;if(!text)return;navigator.clipboard.writeText(text).then(function(){var btn=document.querySelector('.copy-btn[data-target="'+id+'"]');btn.textContent='✓ 已复制';setTimeout(function(){btn.textContent='📋 复制'},2000)})}
var tabs=document.querySelectorAll('.tab-btn');tabs.forEach(function(t){t.addEventListener('click',function(){tabs.forEach(function(x){x.classList.remove('active')});document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('active')});this.classList.add('active');document.getElementById('panel-'+this.dataset.tool).classList.add('active')})});

function showFishCalendar(){
var now=new Date();
var y=now.getFullYear(),m=String(now.getMonth()+1).padStart(2,'0'),d=String(now.getDate()).padStart(2,'0');
var day=now.getDay();
var weekdays=['日','一','二','三','四','五','六'];
var msg='';
if(day===0||day===6){msg='🎉 今天是周末，放心摸鱼！'}
else{
var daysToFri=5-day;
if(daysToFri>0){msg='📅 距离周末还有 '+daysToFri+' 天，加油摸鱼人！'}
else{msg='🎉 已经周五了，明天就周末啦！'}
}
var output='📆 '+y+'年'+m+'月'+d+'日 星期'+weekdays[day]+'\n';
var lunarHint=['初一','初二','初三','初四','初五','初六','初七','初八','初九','初十','十一','十二','十三','十四','十五','十六','十七','十八','十九','二十','廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'];
output+='🏮 农历: 暂略\n';
output+='🐟 摸鱼指数: ';
var fishIdx=Math.floor(Math.random()*40+60)+'%';
output+=fishIdx+'\n';
output+=msg;
document.getElementById('fishOutput').textContent=output;
}

var foodList=[
'火锅','烧烤','麻辣烫','寿司','烤肉','炸鸡','披萨','汉堡','拉面','饺子',
'米线','麻辣香锅','酸菜鱼','小龙虾','串串香','煲仔饭','日式料理','韩式烤肉','越南河粉','印度咖喱',
'粤式点心','川菜','湘菜','西北菜','海鲜自助','牛排','沙拉','轻食便当','烤鱼','螺蛳粉',
'冒菜','干锅','茶餐厅','盖浇饭','炒饭','粥','凉皮','肉夹馍','煎饼果子','部队火锅'
];

function pickFood(){
var idx=Math.floor(Math.random()*foodList.length);
var output='🍽️ 今天吃: '+foodList[idx]+'\n\n';
output+='推荐指数: '+Math.floor(Math.random()*30+70)+'%\n';
output+='建议搭配: ';
var drinks=['可乐','雪碧','柠檬茶','酸梅汤','凉茶','白开水'];
output+=drinks[Math.floor(Math.random()*drinks.length)];
document.getElementById('foodOutput').textContent=output;
}

var pickupLines=[
'你知道我的缺点是什么吗？是缺点你。',
'你上辈子一定是瓶毒药，让我一见到你就中毒。',
'你累不累啊？你都在我心里跑了一整天了。',
'你有没有闻到什么烧焦的味道？那是我的心在为你燃烧。',
'你的脸上有点东西，有什么？有点漂亮。',
'我想买一块地，什么地？你的死心塌地。',
'你是什么血型？A型。不对，你是我的理想型。',
'你会弹吉他吗？那你是怎么拨动了我的心弦？',
'我可以称呼你为您吗？这样我就可以把你放在心上。',
'你知道我最喜欢什么神吗？你的眼神。',
'你的眼睛真好看，不过我的眼睛更好看，因为里面有你。',
'不要抱怨，抱我。',
'这是我的手背，这是我的脚背，你是我的宝贝。',
'你知不知道为什么我感冒了？因为我对你没有抵抗力。',
'你就像那咖啡，我就像那糖，我想把你融化了。',
'我最近在减肥，你知道为什么吗？因为心里装着你，太重了。',
'你的嘴唇上是不是有蜜？怎么那么甜。',
'你知道你和星星有什么区别吗？星星在天上，你在我心里。',
'我喜欢你，就像你妈打你，不讲道理。',
'你能不能借我点钱？干嘛？我想和你凑一对。',
'你知不知道我为什么叫这个名字？因为想和你同名（同命）。',
'别问我为什么对你笑，因为你是我的开心果。',
'你是我见过第二好看的人，第一是谁？是我自己，因为我在照镜子。',
'你可不可以帮我个忙？什么忙？帮我快点喜欢上你。',
'你就像一本书，我读了就不想放下。',
];

function showPickup(){
var idx=Math.floor(Math.random()*pickupLines.length);
document.getElementById('pickupOutput').textContent='💕 '+pickupLines[idx];
}

var complimentList=[
'你简直是人间理想，每一个角度都闪耀着光芒！',
'你的颜值已经突破了人类的极限，美得让人窒息！',
'你不仅长得好看，心地还特别善良，简直是天使下凡！',
'你的笑容比今天的阳光还要温暖，看到你心情就好了！',
'你是我见过最有气质的人，举手投足都散发着魅力！',
'你的聪明才智简直让人佩服，和你聊天总能学到新东西！',
'你今天的状态也太好了吧，整个人都在发光！',
'你怎么可以这么完美，颜值智商情商全部在线！',
'你就是行走的衣架子，穿什么都好看！',
'你的声音也太好听了吧，听你说话是一种享受！',
'你认真做事的样子真的超级迷人，专注的男人/女人最帅/最美！',
'你的审美也太棒了吧，每次都能发现美好的事物！',
'你就是传说中的宝藏女孩/男孩，越了解越喜欢！',
'你的眼睛里有星星，看一眼就让人沦陷！',
'你的品味真的太好了，无论是穿搭还是生活都那么精致！',
'你的人格魅力太大了，和你相处如沐春风！',
'你的皮肤状态也太好了吧，素颜都那么能打！',
'你简直就是行走的荷尔蒙，魅力值爆表！',
'你是个宝藏，认识你是我最大的幸运！',
];

function showCompliment(){
var idx=Math.floor(Math.random()*complimentList.length);
document.getElementById('complimentOutput').textContent='🌈 '+complimentList[idx];
}

function decidePick(){
var text=document.getElementById('decideInput').value.trim();
var options=text.split('\n').filter(function(s){return s.trim()!==''});
if(options.length===0){document.getElementById('decideOutput').textContent='请先输入选项，每行一个';return}
if(options.length===1){document.getElementById('decideOutput').textContent='只有一个选项，没得选啦！就是: '+options[0];return}
var idx=Math.floor(Math.random()*options.length);
var output='🎲 天选之子: '+options[idx]+'\n\n';
output+='总共有 '+options.length+' 个选项\n';
output+='本次选择了第 '+(idx+1)+' 个';
document.getElementById('decideOutput').textContent=output;
}
</script>
