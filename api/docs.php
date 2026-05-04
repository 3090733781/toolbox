<?php
/**
 * API 文档接口
 * 返回所有可用 API 的元数据，供前台 API 文档页面渲染
 */
function handle_docs(&$result, $source, $query, $cfg, $key) {
    $docs = [
        [
            'group' => 'IP 定位',
            'icon' => '📍',
            'apis' => [
                ['source' => 'myip', 'desc' => '获取当前客户端公网 IP', 'params' => [], 'public' => true],
                ['source' => 'ip-api', 'desc' => 'IP 地理位置查询（ip-api.com）', 'params' => [['name' => 'query', 'desc' => 'IP 或域名，留空查本机', 'required' => false]], 'public' => true],
                ['source' => 'ip-sb', 'desc' => 'IP 地理位置查询（ip.sb）', 'params' => [['name' => 'query', 'desc' => 'IP 或域名', 'required' => false]], 'public' => true],
                ['source' => 'ipwhois', 'desc' => 'IP 地理位置查询（ipwho.is）', 'params' => [['name' => 'query', 'desc' => 'IP 或域名', 'required' => false]], 'public' => true],
                ['source' => 'ipip', 'desc' => 'IP 定位（IPIP.net）', 'params' => [], 'public' => true],
                ['source' => 'ip-baidu', 'desc' => '百度 IP 定位（国内）', 'params' => [['name' => 'query', 'desc' => 'IP 地址', 'required' => true]], 'public' => true],
                ['source' => 'ip-baota', 'desc' => '宝塔 IP 定位（含经纬度）', 'params' => [['name' => 'query', 'desc' => 'IP 地址', 'required' => true]], 'public' => true],
                ['source' => 'ip9', 'desc' => 'ip9.com.cn IP 定位（国内）', 'params' => [['name' => 'query', 'desc' => 'IP 地址', 'required' => true]], 'public' => true],
            ],
        ],
        [
            'group' => '域名工具',
            'icon' => '🔍',
            'apis' => [
                ['source' => 'whois', 'desc' => 'WHOIS 域名信息查询', 'params' => [['name' => 'domain', 'desc' => '域名', 'required' => true]], 'public' => true],
                ['source' => 'icp', 'desc' => 'ICP 备案查询（工信部）', 'params' => [['name' => 'domain', 'desc' => '域名/备案号/单位名称', 'required' => true]], 'public' => true],
            ],
        ],
        [
            'group' => '天气',
            'icon' => '🌤',
            'apis' => [
                ['source' => 'weather_amap', 'desc' => '高德地图天气查询', 'params' => [['name' => 'city', 'desc' => '城市名', 'required' => true]], 'public' => true],
            ],
        ],
        [
            'group' => '文件中转',
            'icon' => '📁',
            'apis' => [
                ['source' => 'file_list', 'desc' => '文件列表', 'params' => [], 'public' => true],
                ['source' => 'file_upload', 'desc' => '上传单个文件（POST multipart/form-data）', 'params' => [['name' => 'file', 'desc' => '文件字段', 'required' => true]], 'public' => false, 'method' => 'POST'],
                ['source' => 'file_upload_array', 'desc' => '批量上传文件', 'params' => [['name' => 'file[]', 'desc' => '文件数组', 'required' => true]], 'public' => false, 'method' => 'POST'],
                ['source' => 'file_download', 'desc' => '下载/查看文件', 'params' => [['name' => 'file', 'desc' => '文件名', 'required' => true]], 'public' => true],
                ['source' => 'file_delete', 'desc' => '删除文件', 'params' => [['name' => 'file', 'desc' => '文件名', 'required' => true]], 'public' => false],
            ],
        ],
        [
            'group' => '留言板',
            'icon' => '💬',
            'apis' => [
                ['source' => 'msg_list', 'desc' => '留言列表', 'params' => [], 'public' => true],
                ['source' => 'msg_add', 'desc' => '发表留言（POST JSON）', 'params' => [['name' => 'content', 'desc' => '留言内容', 'required' => true]], 'public' => false, 'method' => 'POST'],
            ],
        ],
        [
            'group' => '用户',
            'icon' => '👤',
            'apis' => [
                ['source' => 'user_info', 'desc' => '获取当前用户信息', 'params' => [], 'public' => false],
                ['source' => 'user_register', 'desc' => '用户注册', 'params' => [['name' => 'username', 'desc' => '用户名', 'required' => true], ['name' => 'password', 'desc' => '密码', 'required' => true]], 'public' => true, 'method' => 'POST'],
                ['source' => 'user_login', 'desc' => '用户登录', 'params' => [['name' => 'username', 'desc' => '用户名', 'required' => true], ['name' => 'password', 'desc' => '密码', 'required' => true]], 'public' => true, 'method' => 'POST'],
            ],
        ],
        [
            'group' => '友链',
            'icon' => '🔗',
            'apis' => [
                ['source' => 'link_list', 'desc' => '友链列表', 'params' => [], 'public' => true],
            ],
        ],
        [
            'group' => '插件',
            'icon' => '🔌',
            'apis' => [
                ['source' => 'plugin_list', 'desc' => '已安装插件列表', 'params' => [], 'public' => true],
            ],
        ],
        [
            'group' => 'API Key 管理',
            'icon' => '🔑',
            'apis' => [
                ['source' => 'apikey_list', 'desc' => '列出自己的所有 API Key', 'params' => [], 'public' => false],
                ['source' => 'apikey_create', 'desc' => '创建新 Key', 'params' => [['name' => 'name', 'desc' => 'Key 名称', 'required' => true], ['name' => 'permissions', 'desc' => '权限（* 或逗号分隔的 source）', 'required' => false], ['name' => 'rate_limit', 'desc' => '每分钟限流（0 不限）', 'required' => false]], 'public' => false, 'method' => 'POST'],
                ['source' => 'apikey_delete', 'desc' => '删除 Key', 'params' => [['name' => 'id', 'desc' => 'Key ID', 'required' => true]], 'public' => false],
                ['source' => 'apikey_toggle', 'desc' => '启用/禁用 Key', 'params' => [['name' => 'id', 'desc' => 'Key ID', 'required' => true]], 'public' => false],
                ['source' => 'apikey_logs', 'desc' => '查看调用日志', 'params' => [['name' => 'key_id', 'desc' => '指定 Key ID', 'required' => false], ['name' => 'page', 'desc' => '页码', 'required' => false]], 'public' => false],
                ['source' => 'apikey_stats', 'desc' => '调用统计', 'params' => [], 'public' => false],
            ],
        ],
    ];

    // 合并插件 API
    $pluginsDir = __DIR__ . '/../plugins';
    if (is_dir($pluginsDir)) {
        $pluginApis = [];
        foreach (scandir($pluginsDir) as $pname) {
            if ($pname === '.' || $pname === '..' || $pname === 'market') continue;
            $mf = "{$pluginsDir}/{$pname}/plugin.json";
            if (!file_exists($mf)) continue;
            $manifest = json_decode(file_get_contents($mf), true);
            $apis = $manifest['api'] ?? [];
            foreach ($apis as $api) {
                $pluginApis[] = [
                    'source' => $api,
                    'desc' => ($manifest['name'] ?? $pname) . '：' . ($manifest['description'] ?? ''),
                    'params' => [],
                    'public' => false,
                    'plugin' => $pname,
                ];
            }
        }
        if ($pluginApis) {
            $docs[] = ['group' => '插件 API', 'icon' => '🧩', 'apis' => $pluginApis];
        }
    }

    $base = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $result['success'] = true;
    $result['data'] = [
        'base_url' => $base . '/api/index.php',
        'auth_methods' => [
            '?key=YOUR_API_KEY',
            'Header: X-API-Key: YOUR_API_KEY',
            'Header: Authorization: Bearer YOUR_API_KEY',
        ],
        'response_format' => [
            'source' => 'string, 接口名',
            'success' => 'bool, 是否成功',
            'data' => 'mixed, 返回数据',
            'error' => 'string|null, 错误信息',
        ],
        'error_codes' => [
            '401' => '无效的 API Key 或未授权',
            '403' => '权限不足',
            '429' => '请求过于频繁',
        ],
        'docs' => $docs,
    ];
}
