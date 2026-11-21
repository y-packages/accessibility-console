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

            case 'WCAG_3_2_2_BUTTON_TYPE':
            case 'ButtonTypeExplicit':
                if (preg_match('/<button\b(?![^>]*\btype=)/i', $snippet)) {
                    $fixed = preg_replace('/<button\b/i', '<button type="button"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added explicit type=\"button\" attribute to prevent unintended form submissions.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_1_1_SVG_FOCUSABLE':
            case 'SvgFocusableIeEdge':
                if (preg_match('/<svg\b(?![^>]*\bfocusable=)/i', $snippet)) {
                    $fixed = preg_replace('/<svg\b/i', '<svg focusable="false"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added focusable=\"false\" to decorative SVG element.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_4_1_2_ARIA_MODAL':
            case 'AriaModalBackdrop':
                if (preg_match('/role=["\'](?:alert)?dialog["\']/i', $snippet) && !preg_match('/\baria-modal=/i', $snippet)) {
                    $fixed = preg_replace('/(role=["\'](?:alert)?dialog["\'])/i', '$1 aria-modal="true"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added aria-modal=\"true\" to custom dialog container.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_4_1_2_ARIA_SWITCH':
            case 'AriaSwitchState':
                if (preg_match('/role=["\']switch["\']/i', $snippet) && !preg_match('/\baria-checked=/i', $snippet)) {
                    $fixed = preg_replace('/(role=["\']switch["\'])/i', '$1 aria-checked="false"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added default aria-checked=\"false\" to role=\"switch\" widget.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_4_8_TEXT_JUSTIFY':
            case 'JustifiedTextBanned':
                if (preg_match('/text-align\s*:\s*justify\b/i', $snippet)) {
                    $fixed = preg_replace('/text-align\s*:\s*justify\b/i', 'text-align: start', $snippet) ?: $snippet;
                    return "EXPLANATION: Replaced text-align: justify with text-align: start for improved readability.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_3_2_HTML_DIR':
            case 'HtmlDirValid':
                if (preg_match('/\bdir=["\'][^"\']*["\']/i', $snippet)) {
                    $fixed = preg_replace('/\bdir=["\'][^"\']*["\']/i', 'dir="auto"', $snippet) ?: $snippet;
                    return "EXPLANATION: Normalized invalid dir attribute to dir=\"auto\".\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_4_1_2_IFRAME_TABINDEX':
            case 'IframeSandboxAccessibility':
                if (preg_match('/<iframe\b[^>]*\btabindex=["\']-\d+["\']/i', $snippet)) {
                    $fixed = preg_replace('/\s+tabindex=["\']-\d+["\']/i', '', $snippet) ?: $snippet;
                    return "EXPLANATION: Removed negative tabindex from visible <iframe> to restore keyboard access.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_4_2_MEDIA_CONTROLS':
            case 'MediaControls':
                if (preg_match('/<(audio|video)\b(?![^>]*\bcontrols\b)/i', $snippet)) {
                    $fixed = preg_replace('/<(audio|video)\b/i', '<$1 controls', $snippet) ?: $snippet;
                    return "EXPLANATION: Added controls attribute to media element for user playback control.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_4_1_2_ARIA_HIDDEN_BODY':
            case 'AriaHiddenBody':
                if (preg_match('/\baria-hidden=["\']true["\']/i', $snippet)) {
                    $fixed = preg_replace('/\s+aria-hidden=["\']true["\']/i', '', $snippet) ?: $snippet;
                    return "EXPLANATION: Removed aria-hidden=\"true\" from document root element.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_2_2_2_MARQUEE':
            case 'MarqueeBanned':
                $fixed = preg_replace('/<\/?marquee\b[^>]*>/i', '<div>', $snippet) ?: $snippet;
                $fixed = preg_replace('/<\/marquee>/i', '</div>', preg_replace('/<marquee\b[^>]*>/i', '<div>', $snippet) ?: $snippet) ?: $snippet;
                return "EXPLANATION: Replaced obsolete <marquee> element with static <div> container.\nFIX: " . $fixed;

            case 'WCAG_FONT_TAG_BANNED':
            case 'FontTagBanned':
                $fixed = preg_replace('/<\/font>/i', '</span>', preg_replace('/<font\b[^>]*>/i', '<span>', $snippet) ?: $snippet) ?: $snippet;
                return "EXPLANATION: Replaced obsolete <font> tag with semantic <span> element.\nFIX: " . $fixed;

            case 'WCAG_1_3_1_DETAILS_SUMMARY':
            case 'DetailsMissingSummary':
                if (preg_match('/<details\b[^>]*>/i', $snippet) && !preg_match('/<summary\b/i', $snippet)) {
                    $fixed = preg_replace('/(<details\b[^>]*>)/i', '$1<summary>Details</summary>', $snippet, 1) ?: $snippet;
                    return "EXPLANATION: Inserted missing <summary> disclosure label inside <details>.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_3_1_FIELDSET':
            case 'FieldsetLegend':
                if (preg_match('/<fieldset\b[^>]*>/i', $snippet) && !preg_match('/<legend\b/i', $snippet)) {
                    $fixed = preg_replace('/(<fieldset\b[^>]*>)/i', '$1<legend>Group</legend>', $snippet, 1) ?: $snippet;
                    return "EXPLANATION: Inserted missing <legend> element inside <fieldset>.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_4_1_2_ARIA_DEPRECATED_ROLE':
            case 'AriaDeprecatedRoles':
                if (preg_match('/\brole=["\']directory["\']/i', $snippet)) {
                    $fixed = preg_replace('/\brole=["\']directory["\']/i', 'role="list"', $snippet) ?: $snippet;
                    return "EXPLANATION: Replaced deprecated role=\"directory\" with role=\"list\".\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_1_3_1_BLOCKQUOTE_MISUSE':
            case 'BlockquoteCiteValid':
                if (preg_match('/\scite=["\']\s*["\']/i', $snippet)) {
                    $fixed = preg_replace('/\scite=["\']\s*["\']/i', '', $snippet) ?: $snippet;
                    return "EXPLANATION: Removed empty cite=\"\" attribute from <blockquote>.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_4_1_2_IFRAME_TITLE':
            case 'IframeTitle':
                if (preg_match('/<iframe\b(?![^>]*\btitle=)/i', $snippet)) {
                    $fixed = preg_replace('/<iframe\b/i', '<iframe title="Embedded Content"', $snippet) ?: $snippet;
                    return "EXPLANATION: Added descriptive title attribute to <iframe>.\nFIX: " . $fixed;
                }
                break;

            case 'WCAG_2_1_1_ACCESSKEY':
            case 'AccesskeyBanned':
                if (preg_match('/\s+accesskey=["\'][^"\']*["\']/i', $snippet)) {
                    $fixed = preg_replace('/\s+accesskey=["\'][^"\']*["\']/i', '', $snippet) ?: $snippet;
                    return "EXPLANATION: Removed conflicting accesskey attribute.\nFIX: " . $fixed;
                }
                break;
        }

        return null;
    }

    /**
     * List of rule IDs supported by deterministic offline self-healing.
     *
     * @return string[]
     */
    public static function getSupportedRuleIds(): array
    {
        return [
            'WCAG_1_1_1_IMG_ALT',
            'WCAG_1_1_1_ImageAlt',
            'WCAG_1_1_1_SVG',
            'WCAG_1_1_1_SVG_FOCUSABLE',
            'WCAG_1_3_1_BLOCKQUOTE_MISUSE',
            'WCAG_1_3_1_DETAILS_SUMMARY',
            'WCAG_1_3_1_FIELDSET',
            'WCAG_1_3_1_OPTGROUP_LABEL',
            'WCAG_1_3_1_PRESENTATION_TAGS',
            'WCAG_1_3_1_TABLE_SCOPE',
            'WCAG_1_3_1_TABLE_SUMMARY',
            'WCAG_1_3_2_HTML_DIR',
            'WCAG_1_4_2_MEDIA_CONTROLS',
            'WCAG_1_4_8_TEXT_JUSTIFY',
            'WCAG_2_1_1_ACCESSKEY',
            'WCAG_2_1_1_SCROLLABLE_FOCUS',
            'WCAG_2_2_1_META_REFRESH',
            'WCAG_2_2_2_BLINK',
            'WCAG_2_2_2_MARQUEE',
            'WCAG_2_4_3_AUTOFOCUS',
            'WCAG_2_4_3_TABINDEX',
            'WCAG_2_4_7_FOCUS_OUTLINE',
            'WCAG_3_1_1_LANG',
            'WCAG_3_2_2_BUTTON_TYPE',
            'WCAG_4_1_2_ARIA_DEPRECATED_ROLE',
            'WCAG_4_1_2_ARIA_HIDDEN_BODY',
            'WCAG_4_1_2_ARIA_MODAL',
            'WCAG_4_1_2_ARIA_SWITCH',
            'WCAG_4_1_2_IFRAME_TABINDEX',
            'WCAG_4_1_2_IFRAME_TITLE',
            'WCAG_FONT_TAG_BANNED',
            'WCAG_LINK_TARGET_BLANK',
        ];
    }
}
