# 工具箱 Toolbox

一款轻量多功能在线工具箱，集成 IP 定位、WHOIS、天气、ICP 备案、文件中转、留言板等常用功能，支持插件扩展与用户/管理员角色分离。

- **当前版本**: v2.8.1

## 功能

- **IP 定位** — 多数据源聚合（ip-api.com、ip.sb、ipwho.is、IPIP.net、百度、宝塔、ip9）
- **WHOIS 查询** — 域名注册信息查询，支持 RDAP 和 WHOIS 直查
- **天气查询** — 基于高德地图 API 的实时天气
- **ICP 备案查询** — 对接工信部备案数据
- **文件中转站** — 拖拽上传、分享文件，支持 API 密钥上传
- **留言板** — 用户留言互动
- **用户中心** — 普通用户管理自己上传的文件
- **管理后台** — 分类管理、插件管理、用户管理、留言管理、文件管理、友链管理、系统配置
- **插件系统** — 第三方插件扩展，自动发现 API 路由
- **插件市场** — 浏览和在线安装插件

## 角色体系

| 角色 | 入口 | 权限 |
|------|------|------|
| 管理员 (admin) | `/admin/` | 全部后台功能 |
| 普通用户 (user) | `/user/` | 我的主页、我的文件 |
| 未登录 | `/` | 浏览前台工具与插件 |

## 安装

1. 将项目文件上传到网站根目录
2. 访问 `http://你的域名/install/` 启动 3 步安装向导
   - 步骤 1：环境检测（PHP 7.4+、cURL、PDO_MySQL、uploads 可写等）
   - 步骤 2：查看更新日志
   - 步骤 3：填写数据库信息，自动建表
3. 安装完成后使用默认管理员 `admin / 123456` 登录，**请立即修改密码**

## 系统要求

- PHP 7.4+
- MySQL 5.6+ / MariaDB 10+
- 扩展：`curl`、`pdo_mysql`、`json`、`session`
- 推荐：`fileinfo`、`ZipArchive`
- `uploads/` 和 `config.json` 需可写

## 目录结构

```
├── admin/          # 管理后台（仅 admin 角色）
├── api/            # API 接口（统一路由 ?source=xxx）
├── user/           # 用户中心（普通用户）
├── install/        # 3 步安装向导
├── modules/        # 功能模块
├── plugins/        # 插件目录
│   └── market/     # 内置插件市场
├── uploads/        # 文件上传目录
├── config.json     # 运行时配置
├── links.json      # 友链数据
└── version.json    # 版本信息
```

## 插件开发

在 `plugins/` 目录下创建插件文件夹，包含 `plugin.json`：

```json
{
  "name": "插件名称",
  "version": "1.0.0",
  "description": "插件说明",
  "author": "作者",
  "type": "tool",
  "icon": "🔌",
  "entry": "index.php",
  "api": ["custom_api"]
}
```

- `entry` 为 API 入口文件，处理函数命名 `handle_{目录名}`
- `api` 数组注册的接口会被动态路由自动发现，无需修改核心代码
- `page.php` 作为前台独立页面（可选）

打包 ZIP 时**必须使用 Python `zipfile`**（Windows 的 `Compress-Archive` 生成反斜杠路径会导致安装器识别失败）：

```python
import zipfile, os
with zipfile.ZipFile('plugin.zip', 'w', zipfile.ZIP_DEFLATED) as z:
    for f in ['plugin.json', 'index.php', 'page.php']:
        z.write(os.path.join('src', f), f'plugin_name/{f}')
```

## API 调用示例

```bash
# 公开 API
curl 'http://your-domain/api/index.php?source=ip-api&query=8.8.8.8'

# 使用 API Key 调用鉴权接口
curl -H 'X-API-Key: your_key' 'http://your-domain/api/index.php?source=file_list'
```

统一返回格式：

```json
{"source": "xxx", "success": true, "data": {...}, "error": null}
```

## 更新日志

详见 [CHANGELOG.md](CHANGELOG.md)。最新版本 v2.8.1 修复了 10+ 项安全漏洞与逻辑错误。

## License

MIT
