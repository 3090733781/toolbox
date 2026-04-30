<?php if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') return; ?>
<div class="stats">
<div class="stat"><div class="stat-num" id="dashFiles">-</div><div class="stat-label">文件数</div></div>
<div class="stat"><div class="stat-num" id="dashUsers">-</div><div class="stat-label">用户数</div></div>
<div class="stat"><div class="stat-num" id="dashPhp"><?= PHP_VERSION ?></div><div class="stat-label">PHP 版本</div></div>
<div class="stat"><div class="stat-num" id="dashSize">-</div><div class="stat-label">上传总量</div></div>
</div>
<script>
fetch("../api/index.php?source=file_list").then(r=>r.json()).then(d=>{if(d.success){document.getElementById("dashFiles").textContent=d.data.length;let t=0;d.data.forEach(f=>t+=f.size);document.getElementById("dashSize").textContent=t>1048576?(t/1048576).toFixed(1)+"MB":(t/1024).toFixed(1)+"KB"}}).catch(()=>{});
fetch("../api/index.php?source=user_list").then(r=>r.json()).then(d=>{if(d.success)document.getElementById("dashUsers").textContent=d.data.length}).catch(()=>{});
</script>
