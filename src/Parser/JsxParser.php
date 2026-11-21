<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Parser;

/**
 * Parses React JSX / TSX files (.jsx, .tsx) by normalizing JSX attributes to standard HTML
 * while preserving exact 1:1 line numbering for accurate violation reporting.
 */
class JsxParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // Normalize JSX attribute names to HTML equivalents
        $replacements = [
            '/\bclassName\s*=/' => 'class=',
            '/\bhtmlFor\s*=/' => 'for=',
            '/\btabIndex\s*=\s*\{(-?\d+)\}/' => 'tabindex="$1"',
            '/\btabIndex\s*=/' => 'tabindex=',
            '/\bonClick\s*=\s*\{[^}]*\}/' => 'onclick="jsxHandler()"',
            '/\bonKeyDown\s*=\s*\{[^}]*\}/' => 'onkeydown="jsxHandler()"',
            '/\bonKeyUp\s*=\s*\{[^}]*\}/' => 'onkeyup="jsxHandler()"',
            '/\bonMouseDown\s*=\s*\{[^}]*\}/' => 'onmousedown="jsxHandler()"',
            '/\bautoFocus\b/' => 'autofocus',
            '/\breadOnly\b/' => 'readonly',
        ];

        $normalized = $content;
        foreach ($replacements as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized) ?? $normalized;
        }

        // Convert attribute={expr} to attribute="JsxExpr" preserving newlines
        $normalized = preg_replace_callback('/\b([a-zA-Z0-9_-]+)\s*=\s*\{([^{}]*)\}/s', function (array $m): string {
            $attr = strtolower($m[1]);
            $inner = trim($m[2]);
            $newlines = str_repeat("\n", substr_count($m[0], "\n"));
            // Preserve string literals inside braces e.g. alt={"Logo"}
            if (preg_match('/^["\'](.*)["\']$/s', $inner, $lit)) {
                return $attr . '="' . htmlspecialchars($lit[1], ENT_QUOTES) . '"' . $newlines;
            }
            if ($inner === 'true' || $inner === 'false') {
                return $attr . '="' . $inner . '"' . $newlines;
            }
            return $attr . '="JsxExpr"' . $newlines;
        }, $normalized) ?? $normalized;

        // Replace JSX text expressions {variable} inside tags with safe text preserving newlines
        $normalized = preg_replace_callback('/>\s*\{([^{}]*)\}\s*</s', function (array $m): string {
            $newlines = str_repeat("\n", substr_count($m[0], "\n"));
            return '>JsxContent' . $newlines . '<';
        }, $normalized) ?? $normalized;

        // Replace JSX fragments <> and </> with <div> and </div>
        $normalized = str_replace(['<>', '</>'], ['<div>', '</div>'], $normalized);

        return $normalized;
    }
}
