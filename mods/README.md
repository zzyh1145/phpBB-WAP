## 插件开发说明

### 1. 插件概述
本说明旨在为phpBB-WAP开发MOD功能，更好的提升用户体验。

### 2. 环境准备
- **phpBB-WAP程序本体**。
- **开发工具**: 需要有PHP开发环境和文本编辑器（如VS Code或Notepad++）。

### 3. 插件结构
在phpBB-WAP的`mods`目录下创建插件文件夹，通常命名为`yourmod`。插件目录结构如下：


```
/mods
    /yourmod
        /install.php
        /uninstall.php
        /yourmod.php
        /template
            /message_body.tpl
            /overall_header.tpl
            /overall_footer.tpl
        /class #可选
            /module_bbcode.php
```

### 4. 核心文件说明
- **install.php**: 插件安装脚本，用于在phpBB-WAP安装时执行。
- **uninstall.php**: 插件卸载脚本，用于在phpBB-WAP卸载时执行。
- **template**: 插件模板文件，用于定义插件的页面布局。
- **class**: 可选，用于定义自定义类，如BBCODE解析器。

### 5. 功能实现
根据需求实现插件功能，如帖子显示、用户互动等，确保兼容WAP格式。

### 6. 数据库操作
如需存储数据，编写相应的SQL语句，并在安装时创建必要的数据库表。

### 7. 插件安装与卸载
- **安装**: 提供安装说明，用户可以通过phpBB-WAP的管理后台安装插件。
- **卸载**: 确保在卸载时清理所有相关数据和设置。

### 8. 测试与调试
在多个设备上测试插件，确保WAP功能正常工作。使用调试工具检查可能的错误和性能问题。

### 9. 文档与支持
提供详细的使用文档，帮助用户了解插件的功能和设置选项，并提供支持渠道以解答用户在使用中的问题。