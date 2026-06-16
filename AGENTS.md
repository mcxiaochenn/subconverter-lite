# AGENTS.md

## 项目概述

**Subconverter Lite** — 轻量化订阅转换工具，PHP 实现。

- 读取 mihomo 配置模板，替换 `proxy-providers` 中的订阅 URL，下发完整配置
- 技术栈：PHP >= 7.4 + curl 扩展，原生 HTML/CSS/JS 前端
- 无 Composer、无数据库、无 yaml 扩展依赖

## 项目结构

```
├── index.php              # 入口（重定向到 index.html）
├── index.html             # 前端页面
├── api.php                # API 入口
├── config.php             # 配置（模板路径、超时、UA）
├── src/
│   ├── Subscription.php   # 订阅类（验证、crc32 生成名称）
│   └── Converter.php      # 转换器（正则替换模板）
├── templates/
│   └── base.yaml          # mihomo 配置模板（有锚点注释）
└── Clash_Sample_Config_By_iKeLee.yaml  # 原始模板备份
```

## 核心逻辑

### API 参数格式

```
# 多个订阅（推荐，最多 10 个）
api.php?sub_url_1=xxx&sub_url_2=xxx&sub_url_3=xxx

# 单个订阅（兼容）
api.php?sub_url=xxx

# 自定义机场名称
api.php?sub_url_1=xxx&name_1=我的机场
```

**关键**：PHP `$_GET` 对同名参数只保留最后一个值，所以用 `sub_url_1`, `sub_url_2` 格式。

### 处理流程

1. `api.php` 接收参数 → 构建订阅信息数组
2. `Subscription::extractName()` 用 `crc32(url)` 生成唯一名称（如 `sub_a1b2c3d4`）
3. `Converter::convert()` 读取模板，用正则替换 `# 锚点 - 节点订阅` 后的 `proxy-providers` 部分
4. 输出 YAML 配置文件

### 模板替换

模板中有锚点注释，PHP 替换这部分内容：

```yaml
# 锚点 - 节点订阅
proxy-providers:
  机场名称1:
    url: '机场1的订阅URL'
    <<: *NodeParam
    ...
```

替换为：

```yaml
# 锚点 - 节点订阅
proxy-providers:
  sub_a1b2c3d4:
    url: 'https://example.com/sub?token=xxx'
    <<: *NodeParam
    ...
```

## 开发规范

- **语言**：交流必须用中文，代码注释优先中文，变量/函数名英文
- **缩进**：4 空格
- **命名**：驼峰（类名、方法名）
- **Git**：Conventional Commits（`feat:` / `fix:` / `docs:` / `refactor:`），默认只 commit 不 push
- **PHP 版本**：兼容 7.4+，不使用高版本特性

## 环境要求

- PHP >= 7.4 + curl 扩展
- Web 服务器（Nginx/Apache）
- 本地开发：PHP CLI 即可，无需 Composer

## 常见问题

### 多订阅只保留一个
**原因**：PHP `$_GET` 对同名参数只保留最后一个值
**解决**：使用 `sub_url_1`, `sub_url_2` 格式

### yaml_parse() 未定义
**原因**：服务器未安装 yaml 扩展
**解决**：本项目不依赖 yaml 扩展，使用纯字符串/正则操作

### mkdir 权限错误
**原因**：之前的版本有缓存功能
**解决**：已移除缓存功能，无需可写目录

## 部署

```nginx
server {
    listen 80;
    server_name sublite.example.com;
    root /var/www/subconverter-lite;
    index index.html index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
