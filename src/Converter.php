<?php
namespace SubconverterLite;

class Converter
{
    private string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    /**
     * 替换模板中的 proxy-providers 部分
     * 
     * @param array $subscriptions 订阅信息数组，每项包含 'name' 和 'url'
     * @return string 替换后的配置内容
     */
    public function convert(array $subscriptions): string
    {
        $template = file_get_contents($this->templatePath);
        
        if ($template === false) {
            throw new \RuntimeException("无法读取模板文件: {$this->templatePath}");
        }
        
        // 生成 proxy-providers 部分
        $providers = $this->generateProviders($subscriptions);
        
        // 替换模板中的 proxy-providers 部分
        return $this->replaceProviders($template, $providers);
    }

    /**
     * 生成 proxy-providers YAML 内容
     */
    private function generateProviders(array $subscriptions): string
    {
        $lines = [];
        
        foreach ($subscriptions as $index => $sub) {
            $name = $sub['name'];
            $url = $sub['url'];
            
            $lines[] = "  {$name}:";
            $lines[] = "    url: '{$url}'";
            $lines[] = "    <<: *NodeParam";
            $lines[] = "    path: './proxy_providers/{$name}.yaml'";
            $lines[] = "    override:";
            $lines[] = "      additional-prefix: \"[{$name}] \" # 为订阅节点添加机场名称前缀";
        }
        
        return implode("\n", $lines);
    }

    /**
     * 替换模板中的 proxy-providers 部分
     */
    private function replaceProviders(string $template, string $newProviders): string
    {
        // 使用正则匹配从 "# 锚点 - 节点订阅" 到下一个顶级段之前的内容
        $pattern = '/(# 锚点 - 节点订阅\n)proxy-providers:.*?(?=\n# 锚点|\nFilter|\Z)/ms';
        
        $replacement = "$1proxy-providers: \n{$newProviders}";
        
        $result = preg_replace($pattern, $replacement, $template);
        
        // 如果正则匹配失败，使用简单的字符串替换
        if ($result === null) {
            // 找到 "proxy-providers:" 的位置
            $startPos = strpos($template, 'proxy-providers:');
            if ($startPos === false) {
                throw new \RuntimeException("模板中未找到 proxy-providers 部分");
            }
            
            // 找到下一个顶级段的位置（以字母开头的行）
            $afterProviders = substr($template, $startPos);
            $lines = explode("\n", $afterProviders);
            $endOffset = 0;
            
            foreach ($lines as $i => $line) {
                if ($i > 0 && preg_match('/^[A-Z]/', $line)) {
                    $endOffset = strpos($afterProviders, $line);
                    break;
                }
            }
            
            if ($endOffset > 0) {
                $before = substr($template, 0, $startPos);
                $after = substr($afterProviders, $endOffset);
                $result = $before . "proxy-providers: \n" . $newProviders . "\n" . $after;
            } else {
                // 如果找不到结尾，替换到末尾
                $before = substr($template, 0, $startPos);
                $result = $before . "proxy-providers: \n" . $newProviders;
            }
        }
        
        return $result;
    }
}
