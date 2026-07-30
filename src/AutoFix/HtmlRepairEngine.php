<?php

namespace YakNet\AccessibilityConsole\AutoFix;

class HtmlRepairEngine
{
    /**
     * Instantly auto-repair common WCAG accessibility issues in raw HTML strings without network calls.
     *
     * @param string $html
     * @return array{repaired_html: string, fixes_applied: array<int, string>}
     */
    public static function autoRepair(string $html): array
    {
        $fixes = [];
        $repaired = $html;

        // 1. Ensure <html lang="..."> attribute
        if (preg_match('/<html\b(?![^>]*\blang=)[^>]*>/i', $repaired)) {
            $repaired = preg_replace('/<html\b([^>]*)>/i', '<html lang="tr"$1>', $repaired) ?? $repaired;
            $fixes[] = 'Added missing lang="tr" attribute to <html> tag.';
        }

        // 2. Add aria-hidden="true" to decorative <svg> tags missing aria-hidden
        $svgBefore = $repaired;
        $repaired = preg_replace_callback('/<svg\b(?![^>]*\baria-hidden=)[^>]*>/i', function($matches) {
            return str_replace('<svg', '<svg aria-hidden="true"', $matches[0]);
        }, $repaired) ?? $repaired;

        if ($repaired !== $svgBefore) {
            $fixes[] = 'Added aria-hidden="true" to decorative <svg> icons.';
        }

        // 3. Add alt="" to <img> tags missing alt attribute
        $imgBefore = $repaired;
        $repaired = preg_replace_callback('/<img\b(?![^>]*\balt=)[^>]*>/i', function($matches) {
            return str_replace('<img', '<img alt=""', $matches[0]);
        }, $repaired) ?? $repaired;

        if ($repaired !== $imgBefore) {
            $fixes[] = 'Added empty alt="" attribute to decorative <img> elements.';
        }

        return [
            'repaired_html' => $repaired,
            'fixes_applied' => $fixes,
        ];
    }
}
