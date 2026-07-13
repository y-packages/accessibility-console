<?php

namespace YakNet\AccessibilityConsole\Core\SelfHealing;

use YakNet\AccessibilityConsole\Core\Violation;

class LocalHealer
{
    /**
     * Attempt to deterministically fix simple violations locally.
     *
     * @param Violation $violation
     * @return string|null
     */
    public function heal(Violation $violation): ?string
    {
        $snippet = $violation->htmlSnippet;
        $ruleId = $violation->ruleId;

        switch ($ruleId) {
            case 'WCAG_2_2_2_BLINK':
                $fixed = str_replace(
                    ['<blink>', '</blink>', '<BLINK>', '</BLINK>'],
                    ['<span style="text-decoration: blink;">', '</span>', '<span style="text-decoration: blink;">', '</span>'],
                    $snippet
                );
                return "EXPLANATION: Banned <blink> tag replaced with a span and text-decoration style.\nFIX: " . $fixed;

            case 'WCAG_2_4_3_AUTOFOCUS':
                $fixed = preg_replace('/\s+autofocus(=["\'](.*?)["\'])?/i', '', $snippet) ?: $snippet;
                return "EXPLANATION: Autofocus attribute removed to prevent screen reader interruption on load.\nFIX: " . $fixed;

            case 'WCAG_1_3_1_TABLE_SUMMARY':
                $fixed = preg_replace('/\s+summary=["\'](.*?)["\']/i', '', $snippet) ?: $snippet;
                return "EXPLANATION: Deprecated table summary attribute removed. Use caption elements instead.\nFIX: " . $fixed;

            case 'WCAG_2_1_1_SCROLLABLE_FOCUS':
                if (!str_contains($snippet, 'tabindex')) {
                    $fixed = preg_replace('/(style=["\'](.*?)["\'])/i', '$1 tabindex="0"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added tabindex=\"0\" to scrollable region to allow keyboard scrolling.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_3_1_PRESENTATION_TAGS':
                if (preg_match('/<center>(.*?)<\/center>/is', $snippet, $matches)) {
                    $fixed = '<div style="text-align: center;">' . $matches[1] . '</div>';
                    return "EXPLANATION: Center element replaced with div and text-align CSS.\nFIX: " . $fixed;
                }
                if (preg_match('/<strike>(.*?)<\/strike>/is', $snippet, $matches)) {
                    $fixed = '<span style="text-decoration: line-through;">' . $matches[1] . '</span>';
                    return "EXPLANATION: Strike element replaced with span and line-through decoration CSS.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_3_1_1_LANG':
                if (preg_match('/<html(\s+[^>]*>|>)/i', $snippet)) {
                    $fixed = preg_replace('/<html/i', '<html lang="en"', $snippet) ?: $snippet;
                    return "EXPLANATION: Injected default lang=\"en\" to HTML element.\nFIX: " . $fixed;
                }
                break;
        }

        return null;
    }
}
