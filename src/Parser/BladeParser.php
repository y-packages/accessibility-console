<?php

namespace YakNet\AccessibilityConsole\Parser;

class BladeParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // Replace {!! ... !!} with static text
        $content = preg_replace('/\{\!\!(.*?)\!\!\}/s', 'StaticRawBladeContent', $content) ?? $content;
        
        // Replace {{ ... }} with static text
        $content = preg_replace('/\{\{\s*(.*?)\s*\}\}/s', 'StaticBladeContent', $content) ?? $content;
        
        // Strip Blade directives like @if, @else, @endif, @foreach, @endforeach, etc.
        $content = preg_replace('/@[a-zA-Z0-9_]+\s*(\(.*?\))?/i', '', $content) ?? $content;
        
        return $content;
    }
}
