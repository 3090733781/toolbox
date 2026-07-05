# 工具箱

一款多功能在线工具箱，集成 IP 定位、WHOIS 查询、天气查询、ICP 备案查询、文件中转、留言板等功能，支持插件扩展。

## 功能

- **IP 定位** - 多数据源 IP 地理位置查询（ip-api.com、ip.sb、IPIP.net、百度、宝塔等）
- **WHOIS 查询** - 域名注册信息查询，支持 RDAP 和 WHOIS 直查
- **天气查询** - 基于高德地图 API 的实时天气查询
- **ICP 备案查询** - 直接对接工信部官网的备案查询
- **文件中转站** - 拖拽上传、分享文件，支持 API 密钥
- **留言板** - 用户留言互动
- **插件系统** - 支持安装第三方插件扩展功能
- **插件市场** - 浏览和安装可用插件

## 安装

1. 将项目文件上传到网站根目录
2. 访问 `http://你的域名/install/` 进入安装向导
3. 按提示配置数据库和管理员密码
4. 安装完成后即可使用

## 系统要求

- PHP 7.4+
- MySQL 5.6+ / MariaDB 10+
- 支持 ZipArchive 扩展（推荐）
- 文件上传权限（用于文件中转）

## 目录结构

```
├── admin/           # 后台管理
├── api/             # API 接口
├── install/         # 安装向导
├── modules/         # 功能模块
├── plugins/         # 插件目录
│   └── market/      # 插件市场
└── uploads/         # 文件上传目录
```

## 插件开发

在 `plugins/` 目录下创建插件文件夹，包含 `plugin.json` 清单文件：

```json
{
  "name": "插件名称",
  "version": "1.0",
  "description": "插件说明",
  "author": "作者",
  "type": "tool",
  "icon": "🔌",
  "entry": "index.php",
  "api": ["custom_api"]
}
```

## License

MIT
