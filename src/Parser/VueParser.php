<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Parser;

/**
 * Parses Vue Single File Components (.vue), extracting the <template> block
 * while preserving exact 1:1 line numbers and normalizing Vue directives for static a11y analysis.
 */
class VueParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // Extract <template> block while preserving leading newlines for exact line mapping
        if (preg_match('/^(.*?)<template\b[^>]*>(.*)<\/template>/is', $content, $match)) {
            $leadingNewlines = str_repeat("\n", substr_count($match[1], "\n"));
            $templateBody = $match[2];
            $cleaned = $this->normalizeVueDirectives($templateBody);
            return $leadingNewlines . $cleaned;
        }

        return $this->normalizeVueDirectives($content);
    }

    private function normalizeVueDirectives(string $html): string
    {
        // Replace mustache interpolations {{ expr }} with safe static placeholder preserving newlines
        $html = preg_replace_callback('/\{\{(.*?)\}\}/s', function (array $m): string {
            return 'VueExpression' . str_repeat("\n", substr_count($m[0], "\n"));
        }, $html) ?? $html;

        // Convert @click="..." / v-on:click="..." to onclick="..."
        $html = preg_replace('/\s(?:@|v-on:)click\s*=\s*"([^"]*)"/i', ' onclick="$1"', $html) ?? $html;
        $html = preg_replace('/\s(?:@|v-on:)keydown(?:\.[a-z0-9]+)*\s*=\s*"([^"]*)"/i', ' onkeydown="$1"', $html) ?? $html;
        $html = preg_replace('/\s(?:@|v-on:)keyup(?:\.[a-z0-9]+)*\s*=\s*"([^"]*)"/i', ' onkeyup="$1"', $html) ?? $html;

        // Convert bound accessibility attributes (:alt, :aria-label, :href, :title, :tabindex, :role, :for)
        $html = preg_replace('/\s(?:v-bind)?\:((?:aria-[a-z-]+|alt|href|title|tabindex|role|for|id|type|name|lang|dir))\s*=\s*"([^"]*)"/i', ' $1="$2"', $html) ?? $html;

        // Strip remaining Vue structural directives (v-if, v-else-if, v-else, v-for, v-show, v-model, v-slot, #slot)
        $html = preg_replace('/\sv-(?:if|else-if|else|for|show|model|html|text|cloak|pre|once|memo|slot)(?:[:.][a-zA-Z0-9_-]+)*(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'))?/i', '', $html) ?? $html;

        return $html;
    }
}
