<?php
namespace SubconverterLite;

class Subscription
{
    private int $timeout;
    private string $userAgent;

    public function __construct(int $timeout = 10, string $userAgent = '')
    {
        $this->timeout = $timeout;
        $this->userAgent = $userAgent ?: 'clash-verge/v2.0.0';
    }

    /**
     * 验证订阅链接是否可访问
     */
    public function validate(string $url): bool
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_NOBODY => true,
        ]);
        
        curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return !$error && $httpCode === 200;
    }

    /**
     * 从 URL 生成唯一名称
     * 使用 crc32 确保不同 token 生成不同名称
     */
    public function extractName(string $url, int $index): string
    {
        // 使用 crc32 生成唯一标识
        $hash = sprintf('%x', crc32($url));
        return "sub_{$hash}";
    }
}
