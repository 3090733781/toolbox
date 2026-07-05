<?php
/**
 * 插件名称：JSON格式化
 * 插件版本：1.0.0
 * 插件说明：JSON格式化与压缩工具，支持错误提示
 */

// 处理API请求
if (isset($_GET['source']) && $_GET['source'] == 'json_format') {
    header('Content-Type: application/json');
    $input = $_POST['input'] ?? '';
    $type = $_POST['type'] ?? 'format'; // format: 格式化, compress: 压缩
    
    if (empty($input)) {
        echo json_encode(['code' => 400, 'msg' => '输入不能为空']);
        exit;
    }
    
    // 解析JSON
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'code' => 400,
            'msg' => 'JSON格式错误：' . json_last_error_msg()
        ]);
        exit;
    }
    
    if ($type == 'format') {
        $output = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        $output = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    echo json_encode([
        'code' => 200,
        'data' => [
            'input' => $input,
            'output' => $output,
            'length' => strlen($output),
            'beautify_length' => strlen(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'compress_length' => strlen(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        ]
    ]);
    exit;
}

// 显示前端页面
?>
<div class="card">
  <div class="card-title">📋 JSON格式化工具</div>
  
  <div class="btns" style="margin-bottom: 20px;">
    <button class="btn active" data-type="format">格式化</button>
    <button class="btn" data-type="compress">压缩</button>
  </div>
  
  <div class="form-group" style="margin-bottom: 20px;">
    <textarea id="json-input" placeholder="请输入JSON字符串" style="width: 100%; height: 200px; padding: 12px; border: 2px solid #e0e4ea; border-radius: 12px; font-size: 14px; resize: vertical; font-family: monospace;"></textarea>
  </div>
  
  <button class="btn" id="convert-btn" style="width: 100%; padding: 12px; font-size: 15px; margin-bottom: 20px;">转换</button>
  
  <div id="result-container" style="display: none;">
    <div class="stats">
      <div class="stat">
        <div class="stat-num" id="stat-original-size"></div>
        <div class="stat-label">原始大小</div>
      </div>
      <div class="stat">
        <div class="stat-num" id="stat-format-size"></div>
        <div class="stat-label">格式化大小</div>
      </div>
      <div class="stat">
        <div class="stat-num" id="stat-compress-size"></div>
        <div class="stat-label">压缩大小</div>
      </div>
    </div>
    
    <div class="card card-sm">
      <div class="card-title" style="font-size: 16px; margin-bottom: 12px;">转换结果</div>
      <textarea id="result-output" readonly style="width: 100%; height: 300px; padding: 12px; border: 2px solid #e0e4ea; border-radius: 12px; font-size: 14px; resize: vertical; background: #f8f9fa; font-family: monospace;"></textarea>
      <button class="btn" id="copy-btn" style="margin-top: 12px;">复制结果</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let currentType = 'format';
  
  // 切换转换类型
  document.querySelectorAll('.btns .btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.btns .btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentType = this.dataset.type;
    });
  });
  
  // 转换按钮点击事件
  document.getElementById('convert-btn').addEventListener('click', function() {
    const input = document.getElementById('json-input').value.trim();
    if (!input) {
      alert('请输入内容');
      return;
    }
    
    fetch('api/index.php?source=json_format', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `input=${encodeURIComponent(input)}&type=${currentType}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.code == 200) {
        document.getElementById('stat-original-size').textContent = formatBytes(data.data.length);
        document.getElementById('stat-format-size').textContent = formatBytes(data.data.beautify_length);
        document.getElementById('stat-compress-size').textContent = formatBytes(data.data.compress_length);
        document.getElementById('result-output').value = data.data.output;
        document.getElementById('result-container').style.display = 'block';
      } else {
        alert(data.msg);
      }
    })
    .catch(err => {
      alert('请求失败：' + err.message);
    });
  });
  
  // 复制按钮点击事件
  document.getElementById('copy-btn').addEventListener('click', function() {
    const output = document.getElementById('result-output');
    output.select();
    document.execCommand('copy');
    alert('已复制到剪贴板');
  });
  
  // 字节格式化
  function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }
});
</script>
