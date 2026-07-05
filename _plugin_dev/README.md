# 插件开发目录

后续新插件统一放在这个目录下开发，每个插件一个独立子目录。

当前结构：

- `two_factor_login/`：二次验证登录插件源码
- `two_factor_login.zip`：可安装/分发的插件压缩包

打包规则：zip 包内保留插件目录本身，例如 `two_factor_login/plugin.json`。
