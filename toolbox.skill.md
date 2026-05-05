# 工具箱 Toolbox - 开发技能

## 项目信息

- **路径**: `C:\Users\18775\Desktop\工具箱\正式版\更新`
- **远程**: `https://github.com/3090733781/toolbox.git`
- **当前版本**: v2.8.1
- **技术栈**: PHP 7.4+ / MySQL / 纯原生 JS + CSS（无框架）
- **版本文件**: `version.json`
- **CHANGELOG**: `CHANGELOG.md`
- **安装方式**: 访问 `/install/` 3 步安装向导
- **线上站点**: http://cx.23zo.cn/

## 目录结构

```
├── admin/              # 管理后台（仅管理员）
│   ├── css/admin.css   # 后台样式
│   ├── index.php       # 后台入口（限制 admin 角色，?p=logout 退出）
│   └── pages/          # 后台页面
│       ├── dashboard.php   # 主页（任何登录用户可用）
│       ├── categories.php  # 分类管理
│       ├── plugins.php     # 插件列表
│       ├── plugins-install.php  # 安装插件
│       ├── users.php       # 用户管理
│       ├── messages.php    # 留言管理
│       ├── files.php       # 文件管理（管理员）
│       ├── my-files.php    # 我的文件（用户可用）
│       ├── links.php       # 友链管理
│       ├── settings.php    # 基本配置
│       └── admin-config.php  # 管理员配置
├── api/                # API 接口
│   ├── index.php       # 统一路由入口 (?source=xxx)，error_reporting(0)，全局 try-catch
│   ├── admin.php       # 后台 API
│   ├── category.php    # 分类 API
│   ├── file.php        # 文件上传/下载/删除 API
│   ├── icp.php         # ICP 备案查询（需外部 API）
│   ├── ip.php          # 7 个 IP 数据源
│   ├── link.php        # 友链 API（JSON 文件存储）
│   ├── market.php      # 插件市场代理
│   ├── message.php     # 留言板 CRUD
│   ├── plugin.php      # 插件安装/卸载 (scanPlugins, deleteDir)
│   ├── storage.php     # 文件存储抽象
│   ├── user.php        # 用户注册/登录/管理
│   ├── weather.php     # 高德天气查询
│   ├── whois.php       # WHOIS 查询
│   └── oauth/          # QQ 彩虹聚合登录 SDK
├── user/               # 用户中心（普通用户）
│   └── index.php       # 用户中心入口（限制 user 角色）
├── install/            # 安装向导
│   └── index.php       # 3步安装（环境检测→更新日志→数据库）
├── modules/            # 功能模块
│   └── messages.php    # 留言板模块（PHP include）
├── plugins/            # 插件目录（第三方插件不纳入 git）
│   ├── market/         # 插件市场（内置）
│   ├── demo/           # 示例插件
│   ├── files/          # 文件中转
│   ├── icp/            # ICP 备案
│   ├── ip/             # IP 定位
│   ├── messages/       # 留言板
│   ├── pwgen/          # 密码生成器
│   ├── weather/        # 天气查询
│   ├── whois/          # WHOIS 查询
│   ├── sky_daily/      # 光遇每日任务（不推送 git）
│   │   ├── plugin.json  # 清单
│   │   ├── index.php    # API 入口 → handle_sky_daily()
│   │   ├── core.php     # 核心逻辑（网易大神数据抓取）
│   │   └── page.php     # 前台页面
│   └── ...             # 其他第三方插件
├── uploads/            # 文件上传目录
├── links.json          # 友链数据（非数据库）
├── config.json         # 系统配置
├── version.json        # 版本信息
├── CHANGELOG.md        # 更新日志
├── index.php           # 前台首页（SPA，内联 CSS+JS ~900行）
└── toolbox.skill.md    # 本技能卡
```

## 数据库结构

```sql
settings (key VARCHAR PK, value TEXT)
users (id, username, password_hash, role, api_key, created_at)
files (id, name, size, type, user_id, uploaded_at)
messages (id, name, content, created_at)
categories (id, name, sort, mode, created_at)
```

各 API 模块内含 `CREATE TABLE IF NOT EXISTS` 自动建表 + `ALTER TABLE ADD COLUMN` 字段迁移。

## 核心 API 路由机制

文件: `api/index.php`

- **入口**: `api/index.php?source=xxx`
- **分发**: `$moduleMap` 映射 source → PHP 模块文件
- **动态路由**: 未匹配 source 扫描 `plugins/` 目录的 `plugin.json` 中的 `api` 数组
- **动态路由流程**: 扫描 plugins → 找到 plugin.json → 匹配 api 数组 → 加载 entry → 调用 `handle_{目录名}()`
- **返回格式**: `{"source": "xxx", "success": true/false, "data": {...}, "error": null}`
- **公开访问**: 所有 API 均公开（白名单全部注释）
- **插件 API**: 动态路由自动发现，无需注册 moduleMap。函数命名 `handle_{目录名}`
- XSS 过滤: 后台用 `esc()` 函数
- 用 `error_reporting(0)` 防止 PHP 警告污染 JSON 输出

## 前台 URL 结构

- 前台首页: `/?p=ip-tool`（支持 `?p=xxx` 独立 URL，支持浏览器前进/后退）
- 管理后台: `/admin/?p=dashboard`（仅管理员）
- 用户中心: `/user/?p=my-files`（普通用户）
- 插件: `/plugins/插件名/page.php`（新标签页打开）
- 插件市场: `/?p=plugin-market`

## 角色分离（v2.8.0）

| 角色 | 访问路径 | 功能 |
|------|---------|------|
| 管理员 (admin) | `/admin/` | 全部管理功能 |
| 普通用户 (user) | `/user/` | 我的主页、我的文件 |
| 未登录 | `/` | 只能浏览前台 |

前台点击用户名按角色自动跳转。

## 退出登录

后台 `?p=logout` 在路由解析前处理（`if ($p === 'logout') { session_destroy(); header('Location: ../'); exit; }`），不会像旧版那样被 `$pages` 数组校验覆写。

## 常见任务

### 1. 创建新插件

```
plugins/插件名/
├── plugin.json   # 清单 (name, version, description, author, type, entry, icon, api, config)
├── page.php      # 前台显示页面（独立页面，不含导航）
└── index.php     # API 入口（需要后端API时）
```

**plugin.json 模板:**
```json
{
  "name": "插件名",
  "version": "1.0.0",
  "description": "功能说明",
  "author": "作者",
  "type": "tool",
  "entry": "index.php",
  "icon": "🔌",
  "api": [],
  "config": false
}
```

**规则:**
- `type: "tool"` 表示有独立页面的工具
- `page.php` 用于前台展示（新标签页打开）
- 如果插件有 API 端点，在 `plugin.json` 的 `api` 数组中注册
- API 处理函数命名: `handle_{目录名}`
- 动态路由自动匹配，无需修改 `api/index.php` 的 moduleMap
- 不要修改工具箱源码，插件完全自包含
- 第三方插件不推送 git

### 2. 插件 Tab 式工具布局

多个工具在一个插件中时使用 tab-bar 切换（不需要独立 page.php，直接在 page.php 内用 tab 划分）：

```html
<style>
.tab-bar{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:20px;background:#fff;border:1px solid #e8ecf1;border-radius:12px;padding:8px}
.tab-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;background:none;color:#666;transition:all .15s}
.tab-btn:hover{background:#f0f2ff;color:#4f6af5}
.tab-btn.active{background:#4f6af5;color:#fff}
.tool-panel{display:none}
.tool-panel.active{display:block}
</style>
<div class="tab-bar">
<button class="tab-btn active" data-tool="json">JSON</button>
<button class="tab-btn" data-tool="base64">Base64</button>
</div>
<div class="tool-panel active" id="panel-json">...</div>
<div class="tool-panel" id="panel-base64">...</div>
<script>
var tabs=document.querySelectorAll('.tab-btn');
tabs.forEach(function(t){t.addEventListener('click',function(){
tabs.forEach(function(x){x.classList.remove('active')});
document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('active')});
this.classList.add('active');
document.getElementById('panel-'+this.dataset.tool).classList.add('active')
})});
</script>
```

### 3. 打包增量更新包

```powershell
$tmpDir="$env:TEMP\update_patch"
New-Item -ItemType Directory -Path "$tmpDir\api","$tmpDir\admin\pages","$tmpDir\plugins\market","$tmpDir\modules" -Force
Copy-Item "源路径\文件" "$tmpDir\对应目录\" -Force
Compress-Archive -Path "$tmpDir\*" -DestinationPath "输出路径\update_版本.zip" -Force
Remove-Item -Recurse -Force $tmpDir
```

### 4. 打包插件 ZIP（重要：必须用 Python，确保正斜杠）

**不要用 PowerShell 的 Compress-Archive**，它在 Windows 上生成反斜杠 `\` 路径，PHP 插件安装器的 `#^([^/]+)/plugin\.json$#` 正则只认正斜杠 `/`，会导致"解压失败：请确认 ZIP 包根目录包含 plugin.json"。

正确方式（Python）：

```python
import zipfile, os
srcdir = r'插件目录路径'
out = r'输出路径\插件名_版本.zip'
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
    for f in ['plugin.json', 'index.php', 'page.php', 'core.php']:
        fp = os.path.join(srcdir, f)
        z.write(fp, f'插件名/{f}')
```

验证 ZIP 条目：
```python
for e in z.namelist():
    print(e)  # 应显示 插件名/xxx.php（正斜杠）
```

### 5. 推送 GitHub

```powershell
git add .
git commit -m "type: 描述"
git push origin main
```

**常用 type:**
- `feat:` 新功能
- `fix:` 修复
- `chore:` 杂项（版本号等）
- `docs:` 文档
- `style:` 样式
- `release:` 发布版本

**注意:** 第三方插件（sky_daily 等）、编译文件（music_unlock/app/）不推送 git。用 `.gitignore` 或 `git rm --cached` 移除已跟踪的文件。

### 6. 更新版本号

1. 修改 `version.json` 中的 `version` 和 `updated` 字段
2. 在 `CHANGELOG.md` 顶部添加新版本记录
3. `git add version.json CHANGELOG.md && git commit -m "release: v版本号 描述" && git push origin main`

## 已完成的插件

| 插件 | 目录 | 功能 |
|------|------|------|
| 🔧 开发工具箱 | dev_tools | JSON/Base64/URL/时间戳/颜色/UUID/正则/手机/身份证/农历/日期 |
| 🔐 编码解码工具箱 | codec_tools | 摩斯/凯撒/ROT13/栅栏/培根/Brainfuck/HTML/Unicode |
| 🔍 实用查询工具 | query_tools | 数字大写/区号邮编/世界各国/亲戚称呼 |
| 🎮 趣味小工具 | fun_tools | 摸鱼日历/今天吃什么/土味情话/彩虹屁/随机决策 |
| 🎵 音乐解锁 | music_unlock | Unlock Music 自部署（不推送 git）|
| 🕯️ 光遇每日任务 | sky_daily | 光遇攻略（不推送 git）|

## 📢 推荐的统一更新内容

以下内容已一次性写入本文件，以提升项目可维护性、文档一致性以及后续开发效率。

### 1️⃣ 版本信息同步提醒
* `version.json` 中的 `version` 与 `updated` 字段应始终与 `CHANGELOG.md` 最顶部记录的版本保持一致。发布新功能或修复时，请先在 `CHANGELOG.md` 添加条目，然后同步 `version.json`，确保两处信息同步。

### 2️⃣ 插件列表自动化维护提示
* 表格列出的插件信息需与 `plugins/` 目录实际内容保持一致。建议在每次新增或删除插件后，手动或使用脚本更新下表，以避免文档与代码不同步。

### 3️⃣ API 路由错误示例与安全说明
* 常见错误返回示例已在 **常见问题排查** 中列出。新增以下内容以帮助快速定位问题：
  * `source` 未匹配时返回 `{"success":false,"error":"source not found"}`
  * `require_once` 失败时返回 `{"success":false,"error":"module load error"}`
* 未来如果计划对部分 API 加入鉴权，请在此处加入 `TODO: add auth check` 标记，以便实现时统一处理。

### 4️⃣ 跨平台插件打包脚本
* 继续使用 Python `zipfile` 打包插件，避免 PowerShell `Compress-Archive` 产生的反斜杠路径问题。
* 额外提供一个可直接在 Windows `cmd`/`PowerShell` 中执行的批处理示例 `tools/zip_plugin.bat`（在项目根目录下），内部调用已安装的 Python 脚本完成打包。

### 5️⃣ FAQ 扩展
* 新增以下常见问题条目：
  * **插件冲突**：当两个插件的 `api` 名称相同或 `plugin.json` 中 `entry` 重复时，系统会以先扫描到的插件为准。解决办法是为每个插件使用唯一的 `api` 前缀或修改 `plugin.json` 中的 `entry` 路径。
  * **本地化支持**：如需多语言插件，可在插件根目录新增 `lang/` 子目录，放置 `zh_CN.json`、`en_US.json` 等语言文件，并在 `page.php` 中读取对应语言键值。

### 6️⃣ 数据库初始化脚本提示
* 项目根目录已提供 `install/setup.sql`（如不存在请自行创建），用于一次性创建/迁移 `settings`、`users`、`files`、`messages`、`categories` 表。建议在 `install/` 文档中加入执行指令示例：`mysql -u root -p < install/setup.sql`。

### 7️⃣ 角色与权限细化
* 当前已定义 `admin`、`user`、`guest` 三类角色。后续若需 `moderator` 等细分角色，请在 `api/*` 中统一使用 `if (hasRole('moderator'))` 的方式进行权限校验，并在本节补充对应说明。

### 8️⃣ 文档格式统一化
* 所有标题、列表已统一使用英文标点。代码块均添加语言标记（```php、```sql、```json 等），便于编辑器高亮。

### 9️⃣ CI / 自动化流程概览（如使用 GitHub Actions）
* 如项目已经配置 CI，请在根目录 `README.md` 中加入以下概述：
  * `test` 工作流运行单元测试（若有）
  * `lint` 工作流检查 PHP 代码风格（PHPCS）
  * `release` 工作流在标签创建后自动生成 `version.json` 并发布 ZIP 包。

### 🔟 README 指向
* 请在项目根目录 `README.md` 首部加入本技能卡的快速链接，例如：`[开发技能文档](toolbox.skill.md)`，帮助新贡献者快速定位项目概览。

---

以上更新已一次性写入，后续如需增删请直接编辑本文件或使用相应脚本自动同步。

## 插件存储路径

- **项目路径**: `C:\Users\18775\Desktop\工具箱\正式版\更新`
- **插件备份/输出**: `C:\Users\18775\Desktop\工具箱\正式版\插件\`
- **旧版备份**: `C:\Users\18775\Desktop\工具箱\正式版\cx_toolbox_clean_v2.7.23 (2)\`

## sky_daily 光遇每日任务插件详情

### 文件结构

```
plugins/sky_daily/
├── plugin.json    # v1.0.4，注册 api: ["sky_daily_fetch"]
├── index.php      # API 路由 → handle_sky_daily()，require_once core.php
├── core.php       # 核心逻辑
│   ├── sky_http_get()     — cURL 封装
│   ├── sky_parse_feed()   — 解析网易大神帖子 HTML
│   ├── sky_fetch_daily()  — 主入口：查缓存 → 抓取 feeds → 匹配每日任务帖 → 解析
│   └── 数据源: https://inf.ds.163.com/v1/web/feed/basic/getSomeOneFeeds
│       ?feedTypes=1,2,3,4,6,7,10,11&someOneUid=0c565eef3c904d84b23f5624ff67f853
└── page.php       # 前台页面（深色主题光遇风格）
```

### 已知问题与修复

1. **`addItem is not a function`** — `addItem` 函数定义在 `if(shard.map){}` 块内。当今天没有碎片（`shard.map` 为空）时 `if` 不执行，函数未定义，但后面的季节蜡烛部分又调用了它。
   - 修复: 将 `function addItem` 定义移到 `if(shard.map)` 之前。

2. **API 返回 `success: false, data: [], error: null`** — 这是默认 `$result` 值，说明 `handle_sky_daily` 根本没有执行。原因通常是缺少 `core.php`（`require_once` 失败 → 函数未定义 → 动态路由跳过）。
   - 修复: 确保 `core.php` 存在于 `plugins/sky_daily/` 目录。

3. **ZIP 安装失败 "根目录包含 plugin.json"** — Windows `Compress-Archive` 使用反斜杠 `\`，PHP 正则只认正斜杠 `/`。
   - 修复: 用 Python 打包，确保条目路径使用正斜杠。

### 数据流

```
page.js → fetch(api/index.php?source=sky_daily_fetch)
       → api/index.php 动态路由
       → include plugins/sky_daily/index.php
       → require_once core.php
       → handle_sky_daily()
       → sky_fetch_daily($date)
         → 检查 cache/{date}.json（TTL 7200s）
         → 抓取网易大神 feeds
         → 匹配每日任务帖（topicName 含"每日任务"）
         → sky_parse_feed() 解析 HTML
         → 返回 {date, calendar, map, season, tasks, shard, sc, bigCandles, magic}
       → 返回 JSON
```

## 常见问题排查

| 现象 | 原因 | 解决 |
|------|------|------|
| 页面一直"加载中" | API 返回 `success: false` | 检查 `api/index.php?source=插件API`，看 handler 是否执行 |
| API 返回 `error: null` | handler 未定义，动态路由跳过 | 检查 `require_once` 路径、文件名拼写 |
| ZIP 安装失败 | 反斜杠路径不匹配正斜杠正则 | 用 Python `zipfile` 打包 |
| 后台退出无效 | `?p=logout` 被 `$pages` 校验覆写 | 在路由解析前处理 `$p === 'logout'` |
| `addItem is not a function` | 函数定义在条件块内 | 移到 `if` 块外面 |
