<?php
/**
 * 插件名称：时间戳转换
 * 插件版本：1.0.0
 * 插件说明：时间戳与日期时间相互转换工具
 */

// 处理API请求
if (isset($_GET['source']) && $_GET['source'] == 'timestamp_convert') {
    header('Content-Type: application/json');
    $input = $_POST['input'] ?? '';
    $type = $_POST['type'] ?? 'timestamp'; // timestamp: 时间戳转日期, datetime: 日期转时间戳
    
    if (empty($input)) {
        echo json_encode(['code' => 400, 'msg' => '输入不能为空']);
        exit;
    }
    
    if ($type == 'timestamp') {
        // 时间戳转日期
        if (!is_numeric($input)) {
            echo json_encode(['code' => 400, 'msg' => '时间戳必须是数字']);
            exit;
        }
        $timestamp = (int)$input;
        // 支持10位和13位时间戳
        if (strlen($input) == 13) {
            $timestamp = $timestamp / 1000;
        }
        $datetime = date('Y-m-d H:i:s', $timestamp);
        echo json_encode([
            'code' => 200,
            'data' => [
                'timestamp' => $input,
                'datetime' => $datetime,
                'iso' => date('c', $timestamp),
                'rfc' => date('r', $timestamp)
            ]
        ]);
    } else {
        // 日期转时间戳
        $timestamp = strtotime($input);
        if ($timestamp === false) {
            echo json_encode(['code' => 400, 'msg' => '日期格式不正确，请输入如：2023-01-01 12:00:00']);
            exit;
        }
        echo json_encode([
            'code' => 200,
            'data' => [
                'datetime' => $input,
                'timestamp' => $timestamp,
                'timestamp_ms' => $timestamp * 1000,
                'iso' => date('c', $timestamp),
                'rfc' => date('r', $timestamp)
            ]
        ]);
    }
    exit;
}

// 显示前端页面
?>
<div class="card">
  <div class="card-title">⏰ 时间戳转换工具</div>
  
  <div class="btns" style="margin-bottom: 20px;">
    <button class="btn active" data-type="timestamp">时间戳转日期</button>
    <button class="btn" data-type="datetime">日期转时间戳</button>
  </div>
  
  <div class="ip-input-wrap">
    <input type="text" id="timestamp-input" placeholder="请输入时间戳（10位或13位）" value="<?= time() ?>">
  </div>
  
  <button class="btn" id="convert-btn" style="width: 100%; padding: 12px; font-size: 15px; margin-bottom: 20px;">转换</button>
  
  <div id="result-container" style="display: none;">
    <div class="card card-sm">
      <div class="row">
        <div class="row-label">输入内容</div>
        <div class="row-value" id="result-input"></div>
      </div>
      <div class="row">
        <div class="row-label">日期时间</div>
        <div class="row-value" id="result-datetime"></div>
      </div>
      <div class="row">
        <div class="row-label">时间戳（10位）</div>
        <div class="row-value" id="result-timestamp"></div>
      </div>
      <div class="row">
        <div class="row-label">时间戳（13位）</div>
        <div class="row-value" id="result-timestamp-ms"></div>
      </div>
      <div class="row">
        <div class="row-label">ISO格式</div>
        <div class="row-value" id="result-iso"></div>
      </div>
      <div class="row">
        <div class="row-label">RFC格式</div>
        <div class="row-value" id="result-rfc"></div>
      </div>
    </div>
  </div>
  
  <div class="card card-sm" style="margin-top: 20px;">
    <div class="card-title" style="font-size: 16px; margin-bottom: 12px;">当前时间</div>
    <div class="row">
      <div class="row-label">当前时间</div>
      <div class="row-value" id="current-datetime"><?= date('Y-m-d H:i:s') ?></div>
    </div>
    <div class="row">
      <div class="row-label">时间戳（10位）</div>
      <div class="row-value" id="current-timestamp"><?= time() ?></div>
    </div>
    <div class="row">
      <div class="row-label">时间戳（13位）</div>
      <div class="row-value" id="current-timestamp-ms"><?= time() * 1000 ?></div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let currentType = 'timestamp';
  
  // 切换转换类型
  document.querySelectorAll('.btns .btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.btns .btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentType = this.dataset.type;
      
      if (currentType == 'timestamp') {
        document.getElementById('timestamp-input').placeholder = '请输入时间戳（10位或13位）';
        document.getElementById('timestamp-input').value = Math.floor(Date.now() / 1000);
      } else {
        document.getElementById('timestamp-input').placeholder = '请输入日期（如：2023-01-01 12:00:00）';
        document.getElementById('timestamp-input').value = new Date().toLocaleString('zh-CN', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        }).replace(/\//g, '-');
      }
    });
  });
  
  // 转换按钮点击事件
  document.getElementById('convert-btn').addEventListener('click', function() {
    const input = document.getElementById('timestamp-input').value.trim();
    if (!input) {
      alert('请输入内容');
      return;
    }
    
    fetch('api/index.php?source=timestamp_convert', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `input=${encodeURIComponent(input)}&type=${currentType}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.code == 200) {
        document.getElementById('result-input').textContent = input;
        document.getElementById('result-datetime').textContent = data.data.datetime;
        document.getElementById('result-timestamp').textContent = data.data.timestamp;
        document.getElementById('result-timestamp-ms').textContent = data.data.timestamp_ms || data.data.timestamp * 1000;
        document.getElementById('result-iso').textContent = data.data.iso;
        document.getElementById('result-rfc').textContent = data.data.rfc;
        document.getElementById('result-container').style.display = 'block';
      } else {
        alert(data.msg);
      }
    })
    .catch(err => {
      alert('请求失败：' + err.message);
    });
  });
  
  // 实时更新当前时间
  setInterval(function() {
    const now = new Date();
    document.getElementById('current-datetime').textContent = now.toLocaleString('zh-CN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    }).replace(/\//g, '-');
    document.getElementById('current-timestamp').textContent = Math.floor(now.getTime() / 1000);
    document.getElementById('current-timestamp-ms').textContent = now.getTime();
  }, 1000);
});
</script>
