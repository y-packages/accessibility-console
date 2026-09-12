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
            case 'WCAG_1_1_1_IMG_ALT':
            case 'WCAG_1_1_1_ImageAlt':
            case 'ImageAlt':
            case 'ImgAltText':
                if (preg_match('/<img\b(?![^>]*\balt=)/i', $snippet)) {
                    $fixed = preg_replace('/<img\b/i', '<img alt=""', $snippet) ?: $snippet;
                    return "EXPLANATION: Injected missing alt=\"\" attribute for decorative image.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_1_1_SVG':
            case 'SvgAltText':
                if (preg_match('/<svg\b(?![^>]*\baria-hidden=)(?![^>]*\baria-label=)/i', $snippet) && !str_contains($snippet, '<title>')) {
                    $fixed = preg_replace('/<svg\b/i', '<svg aria-hidden="true"', $snippet) ?: $snippet;
                    return "EXPLANATION: Injected aria-hidden=\"true\" to decorative SVG element.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_LINK_TARGET_BLANK':
            case 'LinkTargetBlank':
                if (preg_match('/<a\b[^>]*\btarget=["\']_blank["\']/i', $snippet)) {
                    if (preg_match('/rel=["\']([^"\']*)["\']/i', $snippet, $matches)) {
                        $existingRel = $matches[1];
                        $newRel = trim($existingRel . ' noopener noreferrer');
                        $fixed = str_replace($matches[0], 'rel="' . $newRel . '"', $snippet);
                    } else {
                        $fixed = preg_replace('/<a\b/i', '<a rel="noopener noreferrer"', $snippet) ?: $snippet;
                    }
                    return "EXPLANATION: Added rel=\"noopener noreferrer\" to target=\"_blank\" link.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_2_4_3_TABINDEX':
            case 'TabindexOrder':
                if (preg_match('/\btabindex=["\'][1-9]\d*["\']/i', $snippet)) {
                    $fixed = preg_replace('/\btabindex=["\'][1-9]\d*["\']/i', 'tabindex="0"', $snippet) ?: $snippet;
                    return "EXPLANATION: Normalized positive tabindex to tabindex=\"0\" to maintain natural tab order.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_3_1_TABLE_SCOPE':
            case 'ScopeOnlyOnHeaders':
                if (preg_match('/<td\b[^>]*\bscope=["\'][^"\']*["\']/i', $snippet)) {
                    $fixed = preg_replace('/\s+scope=["\'][^"\']*["\']/i', '', $snippet) ?: $snippet;
                    return "EXPLANATION: Removed invalid scope attribute from <td> element.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_2_2_1_META_REFRESH':
            case 'MetaRefreshBanned':
                if (preg_match('/<meta\b[^>]*\bhttp-equiv=["\']refresh["\'][^>]*>/i', $snippet)) {
                    $fixed = '<!-- ' . trim($snippet) . ' (Removed: Meta refresh disorients users) -->';
                    return "EXPLANATION: Commented out disorienting meta refresh tag.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_3_1_OPTGROUP_LABEL':
            case 'OptgroupLabel':
                if (preg_match('/<optgroup\b(?![^>]*\blabel=)/i', $snippet)) {
                    $fixed = preg_replace('/<optgroup\b/i', '<optgroup label="Options"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added fallback label=\"Options\" attribute to <optgroup>.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_2_4_7_FOCUS_OUTLINE':
            case 'NoFocusOutline':
                if (preg_match('/style=["\'][^"\']*outline\s*:\s*(?:none|0)[^"\']*["\']/i', $snippet)) {
                    $fixed = preg_replace('/outline\s*:\s*(?:none|0)/i', 'outline: 2px solid transparent; outline-offset: 2px', $snippet) ?: $snippet;
                    return "EXPLANATION: Replaced outline removal with accessible transparent outline.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_2_2_2_BLINK':
            case 'BlinkTagBanned':
                $fixed = str_replace(
                    ['<blink>', '</blink>', '<BLINK>', '</BLINK>'],
                    ['<span style="text-decoration: blink;">', '</span>', '<span style="text-decoration: blink;">', '</span>'],
                    $snippet
                );
                return "EXPLANATION: Banned <blink> tag replaced with a span and text-decoration style.\nFIX: " . $fixed;

            case 'WCAG_2_4_3_AUTOFOCUS':
            case 'AutofocusBanned':
                $fixed = preg_replace('/\s+autofocus(=["\'](.*?)["\'])?/i', '', $snippet) ?: $snippet;
                return "EXPLANATION: Autofocus attribute removed to prevent screen reader interruption on load.\nFIX: " . $fixed;

            case 'WCAG_1_3_1_TABLE_SUMMARY':
            case 'TableSummaryBanned':
                $fixed = preg_replace('/\s+summary=["\'](.*?)["\']/i', '', $snippet) ?: $snippet;
                return "EXPLANATION: Deprecated table summary attribute removed. Use caption elements instead.\nFIX: " . $fixed;

            case 'WCAG_2_1_1_SCROLLABLE_FOCUS':
            case 'ScrollableRegionFocusable':
                if (!str_contains($snippet, 'tabindex')) {
                    $fixed = preg_replace('/(style=["\'](.*?)["\'])/i', '$1 tabindex="0"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added tabindex=\"0\" to scrollable region to allow keyboard scrolling.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_3_1_PRESENTATION_TAGS':
            case 'PresentationTagsBanned':
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
            case 'HtmlHasLang':
                if (preg_match('/<html(\s+[^>]*>|>)/i', $snippet)) {
                    $fixed = preg_replace('/<html/i', '<html lang="tr"', $snippet) ?: $snippet;
                    return "EXPLANATION: Injected default lang=\"tr\" to HTML element.\nFIX: " . $fixed;
                }
                break;
        }

        return null;
    }
}
