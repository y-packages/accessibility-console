<?php

namespace YakNet\AccessibilityConsole\Parser;

class TwigParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // Strip Twig comments {# ... #}
        $content = preg_replace('/\{\#.*?\#\}/s', '', $content) ?? $content;
        
        // Replace Twig output {{ ... }} with static text
        $content = preg_replace('/\{\{\s*(.*?)\s*\}\}/s', 'StaticTwigOutput', $content) ?? $content;
        
        // Strip Twig statements {% ... %}
        $content = preg_replace('/\{\%.*?\%\}/s', '', $content) ?? $content;
        
        return $content;
    }
}
