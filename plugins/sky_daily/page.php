<?php require_once __DIR__ . '/core.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="referrer" content="no-referrer">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>🕯️ 光遇每日任务攻略</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','PingFang SC','Microsoft YaHei',sans-serif;background:#0f1923;color:#d0d8e0;min-height:100vh}
.header{background:linear-gradient(135deg,#1a3a4a,#0d2137);padding:24px 20px;text-align:center;border-bottom:2px solid #2a5a7a}
.header h1{font-size:22px;color:#ffd700;margin-bottom:4px}
.header .info{font-size:13px;color:#7aa8c0}
.header .info span{margin:0 6px}
.header select{padding:6px 10px;border-radius:6px;border:1px solid #3a6a8a;background:#1a2d3d;color:#ffd700;font-size:13px;margin-top:8px}
.loading{text-align:center;padding:40px;color:#5a8a9a}
.loading::after{content:'...';animation:dots 1.5s infinite}
@keyframes dots{0%,20%{content:'.'}40%{content:'..'}60%,100%{content:'...'}}
.container{max-width:800px;margin:0 auto;padding:16px}
.section{margin-bottom:20px}
.section-title{font-size:18px;color:#ffd700;padding-bottom:8px;border-bottom:2px solid #ffd70033;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.shard-badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:12px;margin-left:6px}
.shard-red{background:#c0392b;color:#fff}
.shard-black{background:#555;color:#ccc}
.task-card{background:#152433;border-radius:8px;padding:14px 16px;margin-bottom:10px;border-left:3px solid #2a6a9a}
.task-card h3{font-size:15px;color:#5cb4ee;margin-bottom:4px}
.task-card .desc{font-size:13px;color:#8aa8be;margin-bottom:2px}
.task-card .loc{font-size:12px;color:#e8a020}
.task-images{display:flex;gap:10px;margin-top:10px;overflow-x:auto}
.task-images img{max-width:48%;max-height:200px;border-radius:6px;cursor:pointer;transition:transform .2s}
.task-images img:hover{transform:scale(1.02)}
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin-bottom:10px}
.info-item{background:#152433;border-radius:6px;padding:8px 12px}
.info-item .lbl{font-size:11px;color:#5a8a9a;margin-bottom:2px}
.info-item .val{font-size:13px;color:#d0d8e0}
.map-images{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px}
.map-images img{width:100%;border-radius:6px;cursor:pointer}
.map-section{margin-bottom:14px}
.map-section h4{font-size:14px;color:#5cb4ee;margin-bottom:6px}
.switch-bar{display:flex;gap:0;margin-bottom:16px}
.switch-btn{flex:1;padding:10px;text-align:center;font-size:13px;cursor:pointer;border:1px solid #2a4a5a;background:#152433;color:#7aa8c0;transition:.2s}
.switch-btn:first-child{border-radius:8px 0 0 8px}
.switch-btn:last-child{border-radius:0 8px 8px 0}
.switch-btn.active{background:#1a4a6a;color:#ffd700;border-color:#2a6a9a}
.hidden{display:none}
.footer{text-align:center;padding:16px;color:#4a6a8a;font-size:11px}
.footer a{color:#5a8a9a}
.back{text-align:center;padding:12px}
.back a{color:#6a8aaa;font-size:13px;text-decoration:none}
/* Lightbox */
.lightbox{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.9);z-index:999;align-items:center;justify-content:center}
.lightbox.active{display:flex}
.lightbox img{max-width:95%;max-height:95%;border-radius:8px}
.lightbox .close{position:absolute;top:20px;right:20px;font-size:30px;color:#fff;cursor:pointer}
</style>
</head>
<body>
<div class="header">
    <h1>🕯️ 光遇每日任务攻略</h1>
    <div class="info" id="headerInfo">加载中...</div>
    <select id="dateSelect" onchange="loadDate(this.value)" style="display:none">
        <option value="">今天</option>
    </select>
</div>
<div class="loading" id="loading">正在连接网易大神获取最新数据</div>
<div class="container hidden" id="content">

    <!-- View switch buttons -->
    <div class="switch-bar" id="switchBar">
        <button class="switch-btn active" data-view="tasks">📋 每日任务</button>
        <button class="switch-btn" data-view="shard">💥 碎片</button>
        <button class="switch-btn" data-view="candles">🕯️ 大蜡烛</button>
    </div>

    <!-- Tasks View -->
    <div class="section" id="viewTasks">
        <div class="section-title">📋 每日任务</div>
        <div id="tasksList"></div>
    </div>

    <!-- Shard View -->
    <div class="section hidden" id="viewShard">
        <div class="section-title">💥 伊甸之眼坠落碎片</div>
        <div id="shardContent"></div>
    </div>

    <!-- Candles View -->
    <div class="section hidden" id="viewCandles">
        <div class="section-title">🕯️ 大蜡烛攻略</div>
        <div id="candlesContent"></div>
    </div>

    <div class="section">
        <div class="section-title">✨ 今日免费魔法</div>
        <div id="magicContent"></div>
    </div>

    <div class="back"><a href="javascript:history.back()">← 返回工具箱</a></div>
</div>

<div class="lightbox" id="lightbox" onclick="closeLB()">
    <span class="close">&times;</span>
    <img id="lbImg" src="">
</div>

<div class="footer">
    数据来源: <a href="https://ds.163.com/user/<?=SKY_UID?>">网易大神 - Sky孤独旅人</a> | 插件 v1.0
</div>

<script>
var currentDate = '<?=date('Y-m-d')?>';
var data = null;

function showLoading(){document.getElementById('loading').style.display='block';document.getElementById('content').classList.add('hidden')}
function hideLoading(){document.getElementById('loading').style.display='none';document.getElementById('content').classList.remove('hidden')}

function loadDate(date){
    currentDate = date || currentDate;
    showLoading();
    // Build API URL: go up 2 levels from plugins/sky_daily/ to toolbox root
    var apiBase = location.href.replace(/\/plugins\/sky_daily\/page\.php.*/, '') + '/api/index.php';
    fetch(apiBase + '?source=sky_daily_fetch&date=' + encodeURIComponent(currentDate))
        .then(function(r){return r.json()})
        .then(function(r){
            if(!r.success){alert(r.error||'加载失败');hideLoading();return}
            data = r.data;
            render();
            hideLoading();
        })
        .catch(function(e){alert('请求失败: '+e.message);hideLoading()});
}

function render(){
    if(!data) return;
    // Header info
    var h = [];
    if(data.date) h.push('📅 '+data.date);
    if(data.calendar) h.push('农历: '+data.calendar);
    if(data.map) h.push('🗺️ '+data.map);
    if(data.season) h.push('🎭 '+data.season);
    document.getElementById('headerInfo').innerHTML = h.map(function(s){return '<span>'+s+'</span>'}).join('');

    // Tasks
    var taskHTML = '';
    if(data.tasks && data.tasks.length>0){
        data.tasks.forEach(function(t,i){
            taskHTML += '<div class="task-card">';
            taskHTML += '<h3>'+(i+1)+'. '+escHtml(t.name)+'</h3>';
            if(t.desc) taskHTML += '<div class="desc">📝 '+escHtml(t.desc)+'</div>';
            if(t.loc) taskHTML += '<div class="loc">📍 '+escHtml(t.loc)+'</div>';
            if(t.images && t.images.length>0){
                taskHTML += '<div class="task-images">';
                t.images.forEach(function(img){
                    taskHTML += '<img src="'+img+'" onclick="openLB(this.src)" alt="任务图">';
                });
                taskHTML += '</div>';
            }
            taskHTML += '</div>';
        });
    }
    document.getElementById('tasksList').innerHTML = taskHTML||'<p style="color:#5a8a9a">暂无任务数据</p>';

    // Shard info
    var shard = data.shard || {};
    var shardHTML = '';
    if(shard.map){
        shardHTML += '<div class="info-grid">';
        function addItem(l,v){if(v)shardHTML+='<div class="info-item"><div class="lbl">'+l+'</div><div class="val">'+escHtml(v)+'</div></div>'}
        addItem('地图',shard.map);
        addItem('类型',shard.type);
        addItem('位置',shard.location);
        addItem('时间',shard.time);
        addItem('清理',shard.method);
        addItem('货币',shard.reward);
        shardHTML += '</div>';
        if(shard.images && shard.images.length>0){
            shardHTML += '<div class="map-images">';
            shard.images.forEach(function(img){shardHTML+='<img src="'+img+'" onclick="openLB(this.src)">'});
            shardHTML += '</div>';
        }
    }else{shardHTML='<p style="color:#5a8a9a">今日无碎片</p>'}
    document.getElementById('shardContent').innerHTML = shardHTML;

    // Seasonal candles (also show in shard section)
    if(data.sc && data.sc.map){
        shardHTML += '<div style="margin-top:14px"><div class="section-title">🕯️ 季节蜡烛</div>';
        shardHTML += '<div class="info-grid">';
        addItem('地图',data.sc.map);
        addItem('位置组',data.sc.group);
        shardHTML += '</div>';
        if(data.sc.images && data.sc.images.length>0){
            shardHTML += '<div class="map-images">';
            data.sc.images.forEach(function(img){shardHTML+='<img src="'+img+'" onclick="openLB(this.src)">'});
            shardHTML += '</div>';
        }
        shardHTML += '</div>';
    }
    document.getElementById('shardContent').innerHTML = shardHTML;

    // Big candles
    var candleHTML = '';
    if(data.bigCandles && data.bigCandles.length>0){
        data.bigCandles.forEach(function(bc){
            candleHTML += '<div class="map-section">';
            candleHTML += '<h4>🗺️ '+bc.map+'</h4>';
            if(bc.images && bc.images.length>0){
                candleHTML += '<div class="map-images">';
                bc.images.forEach(function(img){candleHTML+='<img src="'+img+'" onclick="openLB(this.src)">'});
                candleHTML += '</div>';
            }
            candleHTML += '</div>';
        });
    }
    document.getElementById('candlesContent').innerHTML = candleHTML||'<p style="color:#5a8a9a">暂无数据</p>';

    // Magic
    document.getElementById('magicContent').innerHTML = data.magic ? '<div class="info-item"><div class="val" style="font-size:15px">'+escHtml(data.magic)+'</div></div>' : '<p style="color:#5a8a9a">暂无</p>';
}

function escHtml(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML}

// View switching
document.getElementById('switchBar').addEventListener('click',function(e){
    if(!e.target.classList.contains('switch-btn')) return;
    var v = e.target.dataset.view;
    document.querySelectorAll('.switch-btn').forEach(function(b){b.classList.toggle('active',b===e.target)});
    document.getElementById('viewTasks').classList.toggle('hidden',v!=='tasks');
    document.getElementById('viewShard').classList.toggle('hidden',v!=='shard');
    document.getElementById('viewCandles').classList.toggle('hidden',v!=='candles');
});

function openLB(src){document.getElementById('lbImg').src=src;document.getElementById('lightbox').classList.add('active')}
function closeLB(){document.getElementById('lightbox').classList.remove('active')}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeLB()});

// Init
loadDate();
</script>
</body>
</html>
