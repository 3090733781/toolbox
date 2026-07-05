<?php
/**
 * 插件名称：Base64编码解码
 * 插件版本：1.0.0
 * 插件说明：文本与Base64编码相互转换工具
 */

// 处理API请求
if (isset($_GET['source']) && $_GET['source'] == 'base64_convert') {
    header('Content-Type: application/json');
    $input = $_POST['input'] ?? '';
    $type = $_POST['type'] ?? 'encode'; // encode: 编码, decode: 解码
    
    if (empty($input)) {
        echo json_encode(['code' => 400, 'msg' => '输入不能为空']);
        exit;
    }
    
    if ($type == 'encode') {
        $output = base64_encode($input);
        echo json_encode([
            'code' => 200,
            'data' => [
                'input' => $input,
                'output' => $output
            ]
        ]);
    } else {
        $output = base64_decode($input, true);
        if ($output === false) {
            echo json_encode(['code' => 400, 'msg' => '无效的Base64编码']);
            exit;
        }
        // 尝试检测编码
        $encoding = mb_detect_encoding($output, ['UTF-8', 'GBK', 'GB2312', 'BIG5']);
        if ($encoding && $encoding != 'UTF-8') {
            $output = mb_convert_encoding($output, 'UTF-8', $encoding);
        }
        echo json_encode([
            'code' => 200,
            'data' => [
                'input' => $input,
                'output' => $output
            ]
        ]);
    }
    exit;
}

// 显示前端页面
?>
<div class="card">
  <div class="card-title">🔢 Base64编码解码工具</div>
  
  <div class="btns" style="margin-bottom: 20px;">
    <button class="btn active" data-type="encode">编码（文本转Base64）</button>
    <button class="btn" data-type="decode">解码（Base64转文本）</button>
  </div>
  
  <div class="form-group" style="margin-bottom: 20px;">
    <textarea id="base64-input" placeholder="请输入要转换的内容" style="width: 100%; height: 120px; padding: 12px; border: 2px solid #e0e4ea; border-radius: 12px; font-size: 14px; resize: vertical;"></textarea>
  </div>
  
  <button class="btn" id="convert-btn" style="width: 100%; padding: 12px; font-size: 15px; margin-bottom: 20px;">转换</button>
  
  <div id="result-container" style="display: none;">
    <div class="card card-sm">
      <div class="card-title" style="font-size: 16px; margin-bottom: 12px;">转换结果</div>
      <textarea id="result-output" readonly style="width: 100%; height: 120px; padding: 12px; border: 2px solid #e0e4ea; border-radius: 12px; font-size: 14px; resize: vertical; background: #f8f9fa;"></textarea>
      <button class="btn" id="copy-btn" style="margin-top: 12px;">复制结果</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let currentType = 'encode';
  
  // 切换转换类型
  document.querySelectorAll('.btns .btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.btns .btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentType = this.dataset.type;
      
      if (currentType == 'encode') {
        document.getElementById('base64-input').placeholder = '请输入要编码的文本';
      } else {
        document.getElementById('base64-input').placeholder = '请输入要解码的Base64编码';
      }
    });
  });
  
  // 转换按钮点击事件
  document.getElementById('convert-btn').addEventListener('click', function() {
    const input = document.getElementById('base64-input').value.trim();
    if (!input) {
      alert('请输入内容');
      return;
    }
    
    fetch('api/index.php?source=base64_convert', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `input=${encodeURIComponent(input)}&type=${currentType}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.code == 200) {
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
});
</script>
