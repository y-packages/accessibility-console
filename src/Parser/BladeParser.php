<?php

namespace YakNet\AccessibilityConsole\Parser;

class BladeParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // 1. Strip Blade comments {{-- ... --}} while preserving line count
        $content = preg_replace_callback('/\{\{--(.*?)--\}\}/s', function ($m) {
            return str_repeat("\n", substr_count($m[0], "\n"));
        }, $content) ?? $content;

        // 2. Replace raw echo {!! ... !!} while preserving line count
        $content = preg_replace_callback('/\{\!\!(.*?)\!\!\}/s', function ($m) {
            $newlines = str_repeat("\n", substr_count($m[0], "\n"));
            return 'StaticRawBladeContent' . $newlines;
        }, $content) ?? $content;

        // 3. Replace safe echo {{ ... }} while preserving line count
        $content = preg_replace_callback('/\{\{\s*(.*?)\s*\}\}/s', function ($m) {
            $newlines = str_repeat("\n", substr_count($m[0], "\n"));
            return 'StaticBladeContent' . $newlines;
        }, $content) ?? $content;

        // 4. Strip Blade directives like @if, @else, @endif, @foreach, @component, etc.
        $content = preg_replace_callback('/@[a-zA-Z0-9_]+\s*(\([^\)]*\))?/s', function ($m) {
            return str_repeat("\n", substr_count($m[0], "\n"));
        }, $content) ?? $content;

        return $content;
    }
}
