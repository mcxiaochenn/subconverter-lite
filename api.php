<?php
require_once __DIR__ . '/src/Subscription.php';
require_once __DIR__ . '/src/Converter.php';

use SubconverterLite\Subscription;
use SubconverterLite\Converter;

// 加载配置
$config = require __DIR__ . '/config.php';

// 获取订阅 URL 列表（支持 sub_url_1, sub_url_2, ... 或 sub_url）
$subscriptionUrls = [];
$providerNames = [];

for ($i = 1; $i <= 10; $i++) {
    if (isset($_GET["sub_url_{$i}"])) {
        $subscriptionUrls[] = $_GET["sub_url_{$i}"];
        if (isset($_GET["name_{$i}"])) {
            $providerNames[] = $_GET["name_{$i}"];
        }
    }
}

// 兼容单个 sub_url 参数
if (empty($subscriptionUrls) && isset($_GET['sub_url'])) {
    $subscriptionUrls = [$_GET['sub_url']];
    if (isset($_GET['name'])) {
        $providerNames = [$_GET['name']];
    }
}

if (empty($subscriptionUrls)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => '缺少订阅链接参数',
        'usage' => '请使用 ?sub_url_1=订阅链接 格式访问',
        'example' => [
            '单个订阅' => 'https://your-domain.com/api.php?sub_url_1=https://example.com/sub1',
            '多个订阅' => 'https://your-domain.com/api.php?sub_url_1=https://example.com/sub1&sub_url_2=https://example.com/sub2',
            '自定义名称' => 'https://your-domain.com/api.php?sub_url_1=https://example.com/sub1&name_1=我的机场',
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 初始化组件
$subscription = new Subscription($config['request_timeout'], $config['user_agent']);
$converter = new Converter($config['template_path']);

try {
    // 构建订阅信息数组
    $subscriptions = [];
    
    foreach ($subscriptionUrls as $index => $url) {
        // 获取机场名称：优先使用用户传入的，否则从 URL 中提取
        $name = $providerNames[$index] ?? $subscription->extractName($url, $index);
        
        $subscriptions[] = [
            'name' => $name,
            'url' => $url,
        ];
    }
    
    // 替换模板中的 proxy-providers
    $configContent = $converter->convert($subscriptions);
    
    // 输出配置
    outputConfig($configContent);
    
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => '处理失败',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function outputConfig(string $content): void
{
    header('Content-Type: application/x-yaml; charset=utf-8');
    header('Content-Disposition: attachment; filename="config.yaml"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $content;
}
