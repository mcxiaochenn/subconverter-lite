# Subconverter Lite

极其轻量化的 subconverter 项目，基于 PHP 实现。

## 功能特点

- 轻量化设计，无需额外扩展
- 简洁美观的前端界面
- 支持 Clash yaml 格式订阅
- 支持多订阅链接合并（最多 10 个）
- 支持多种代理协议：SS、SSR、VMess、VLESS、Trojan、Hysteria 等
- 基于 mihomo 配置模板

## 环境要求

- PHP >= 7.4
- PHP curl 扩展

## 安装

1. 克隆项目到你的 Web 服务器目录：
```bash
git clone https://github.com/mcxiaochenn/subconverter-lite.git
```

2. 确保 PHP curl 扩展已安装：
```bash
# Ubuntu/Debian
sudo apt-get install php-curl

# CentOS/RHEL
sudo yum install php-curl
```

## 使用方法

### 方式一：前端页面（推荐）

直接访问你的域名，会显示一个简洁的前端页面：

```
https://your-domain.com/
```

在页面中输入订阅链接，点击"生成配置"按钮，即可获取订阅地址。

### 方式二：API 直接调用

#### 多个订阅（推荐）

```
https://your-domain.com/api.php?sub_url_1=订阅链接1&sub_url_2=订阅链接2&sub_url_3=订阅链接3
```

#### 单个订阅（兼容）

```
https://your-domain.com/api.php?sub_url=你的订阅链接
```

#### 自定义机场名称

```
https://your-domain.com/api.php?sub_url_1=订阅链接1&name_1=我的机场
```

## 配置说明

编辑 `config.php` 文件可以修改以下配置：

```php
return [
    // 模板文件路径
    'template_path' => __DIR__ . '/templates/base.yaml',
    
    // 请求超时时间（秒）
    'request_timeout' => 10,
    
    // 用户代理
    'user_agent' => 'clash-verge/v2.0.0',
];
```

## 自定义模板

模板文件位于 `templates/base.yaml`，基于 mihomo 配置格式。你可以根据需要修改模板。

## 项目结构

```
subconverter-lite/
├── index.php              # 入口文件（重定向到前端）
├── index.html             # 前端页面
├── api.php                # API 入口
├── config.php             # 配置文件
├── src/
│   ├── Subscription.php   # 订阅解析类
│   └── Converter.php      # 转换器类
├── templates/
│   └── base.yaml          # 基础模板
├── Clash_Sample_Config_By_iKeLee.yaml  # 原始模板备份
└── README.md
```

## 模板来源

本项目的 mihomo 配置模板基于 [可莉的 ProxyResource](https://github.com/luestr/ProxyResource/blob/main/Tool/Clash/Config/Clash_Sample_Config_By_iKeLee.yaml) 项目中的 `Clash_Sample_Config_By_iKeLee.yaml` 配置文件。

原始配置作者：[iKeLee](https://t.me/iKeLee)

## 许可证

MIT License
