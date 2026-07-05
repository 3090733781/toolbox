<?php
/**
 * 插件名称：示例插件
 * 插件版本：1.0.0
 * 插件说明：插件开发模板，包含完整注释
 * 
 * =============================================
 *  🧩 工具箱插件开发指南
 * =============================================
 * 
 * 【目录结构】
 *   plugins/你的插件名/
 *   ├── plugin.json    ← 插件清单（必需）
 *   ├── index.php      ← 主逻辑文件（可选）
 *   ├── page.php       ← 前端页面（可选）
 *   └── ...其他文件
 * 
 * 【plugin.json 字段说明】
 *   name        - 插件名称（显示在后台）
 *   version     - 版本号
 *   description - 插件说明
 *   author      - 作者
 *   type        - 类型: "tool"=工具 "service"=服务
 *   entry       - 入口文件（默认 index.php）
 *   icon        - 显示图标（emoji）
 *   api         - 注册的 API 端点数组（可选）
 *   config      - 是否有后台设置页（true/false）
 * 
 * 【API 端点注册】
 *   在 plugin.json 的 api 数组中列出端点名称，
 *   例如 ["demo_query"] 会注册 demo_query 端点。
 *   前端可通过 fetch('api/index.php?source=demo_query&参数=值')
 *   调用此插件。
 * 
 * 【数据库使用】
 *   插件可以创建自己的数据库表，建议表名前缀为插件名：
 *   CREATE TABLE IF NOT EXISTS `plugin_demo_data` (...)
 * 
 * 【前端页面】
 *   插件可以提供前端页面，访问方式取决于入口文件：
 *   通过 include 加载：在 index.php 中输出 HTML
 */

// =============================================
//  插件主逻辑
// =============================================

/**
 * 插件入口函数
 * 
 * 当该插件需要处理 API 请求时，系统会自动调用此函数。
 * 参数说明：
 *   &$result  - 返回给前端的结果数组
 *   $source   - 当前请求的 API 端点名
 *   $query    - 用户输入的查询参数
 *   $cfg      - 系统配置（config.json）
 *   $key      - 高德地图 API Key
 * 
 * 返回格式：
 *   $result['success'] = true/false;
 *   $result['data']    = 返回的数据;
 *   $result['error']   = 错误信息;
 */
function handle_demo(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'demo_query':
            // 示例：接收参数并返回结果
            $input = $_GET['q'] ?? 'world';
            $result['success'] = true;
            $result['data'] = [
                'message' => 'Hello, ' . $input . '!',
                'time' => date('Y-m-d H:i:s'),
                'plugin' => 'demo',
                'version' => '1.0.0',
            ];
            return;

        // 可以注册更多 API 端点
        // case 'demo_upload': ...
        // case 'demo_delete': ...
    }
}

// =============================================
//  插件页面输出（在后台插件列表中显示设置页）
// =============================================

/**
 * 当 config 设为 true 时，可以在后台插件列表中显示设置内容。
 * 通过 switchPage 到 plugins 页面时，插件设置会显示。
 * 
 * 设置数据的保存：
 *   使用 admin_save API 保存到 config.json
 *   示例：fetch('api/index.php?source=admin_save', {...})
 * 
 * 设置数据的读取：
 *   使用 file_config API 读取
 *   示例：fetch('api/index.php?source=file_config')
 */
?>
<div class="card">
    <div class="card-title">⚙ 示例插件配置</div>
    <div class="form-group">
        <label>欢迎语</label>
        <input type="text" id="demoGreeting" value="你好，工具箱！" 
               style="width:100%;padding:9px 12px;border:1px solid #d0d5dd;border-radius:6px;font-size:13px;outline:none">
    </div>
    <button class="btn btn-primary" onclick="saveDemoConfig()">保存</button>
</div>

<script>
// 保存插件配置（保存到 config.json 的插件专属字段）
function saveDemoConfig() {
    var greeting = document.getElementById('demoGreeting').value;
    fetch('../api/index.php?source=admin_save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            plugin_demo: { greeting: greeting }
        })
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.success) alert('保存成功');
        else alert('保存失败：' + d.error);
    });
}
</script>
