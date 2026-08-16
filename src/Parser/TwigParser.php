<?php

namespace YakNet\AccessibilityConsole\Parser;

class TwigParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // 1. Strip Twig comments {# ... #} while preserving line count
        $content = preg_replace_callback('/\{\#.*?\#\}/s', function ($m) {
            return str_repeat("\n", substr_count($m[0], "\n"));
        }, $content) ?? $content;

        // 2. Replace Twig output {{ ... }} while preserving line count
        $content = preg_replace_callback('/\{\{\s*(.*?)\s*\}\}/s', function ($m) {
            $newlines = str_repeat("\n", substr_count($m[0], "\n"));
            return 'StaticTwigOutput' . $newlines;
        }, $content) ?? $content;

        // 3. Strip Twig statements {% ... %} while preserving line count
        $content = preg_replace_callback('/\{\%.*?\%\}/s', function ($m) {
            return str_repeat("\n", substr_count($m[0], "\n"));
        }, $content) ?? $content;

        return $content;
    }
}
