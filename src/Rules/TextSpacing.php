<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TextSpacing extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_4_12_TEXT_SPACING';
    }

    public function getDescription(): string
    {
        return 'Ensure text containers do not restrict line-height or clip content when text spacing is adjusted (WCAG 1.4.12).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('style')) {
            return null;
        }

        $style = strtolower($element->getAttribute('style'));

        // Check line-height < 1.2 or <= 12px
        if (preg_match('/line-height\s*:\s*([^;]+)/i', $style, $matches)) {
            $lh = trim($matches[1]);
            // Unitless number check
            if (is_numeric($lh) && (float)$lh < 1.2 && (float)$lh > 0) {
                return $this->createViolation(
                    $element,
                    "Inline line-height is set to {$lh}, which is below the recommended minimum of 1.2 to 1.5.",
                    "Increase line-height to at least 1.5 to satisfy WCAG 1.4.12 Text Spacing."
                );
            }
            // Pixel check (e.g. 10px, 12px)
            if (preg_match('/^(\d+(?:\.\d+)?)px$/i', $lh, $pxMatches)) {
                $px = (float)$pxMatches[1];
                if ($px < 14) {
                    return $this->createViolation(
                        $element,
                        "Inline line-height is fixed to {$px}px, risking text overlapping when resized.",
                        "Use relative line-height units (e.g. 1.5) instead of fixed small pixel values."
                    );
                }
            }
        }

        // Check fixed height combined with overflow: hidden on text containers
        $textTags = ['p', 'div', 'span', 'article', 'section', 'blockquote', 'li'];
        if (in_array(strtolower($element->tagName), $textTags, true)) {
            if (str_contains($style, 'overflow:hidden') || str_contains($style, 'overflow: hidden')) {
                if (preg_match('/(?:^|;)\s*height\s*:\s*(\d+)px/i', $style, $heightMatches)) {
                    $height = (int)$heightMatches[1];
                    $textLength = mb_strlen(trim($element->textContent));
                    if ($height <= 60 && $textLength > 60) {
                        return $this->createViolation(
                            $element,
                            "Fixed height ({$height}px) with overflow:hidden risks clipping text when user overrides text spacing.",
                            "Use min-height instead of fixed height or avoid overflow:hidden on text containers."
                        );
                    }
                }
            }
        }

        return null;
    }
}
