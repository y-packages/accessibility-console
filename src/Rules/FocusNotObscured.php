<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FocusNotObscured extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_4_11_FOCUS_NOT_OBSCURED';
    }

    public function getDescription(): string
    {
        return 'Fixed, sticky, or overlay elements must not completely obscure keyboard-focused controls (WCAG 2.2 SC 2.4.11).';
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
        $style = strtolower($element->getAttribute('style'));
        if (trim($style) === '') {
            return null;
        }

        $isFixedOrSticky = str_contains($style, 'position:fixed') || str_contains($style, 'position: fixed') ||
                           str_contains($style, 'position:sticky') || str_contains($style, 'position: sticky');

        if (!$isFixedOrSticky) {
            return null;
        }

        // Check if element is a top/bottom bar with high z-index without scroll-padding considerations
        $hasTopOrBottom = str_contains($style, 'top:0') || str_contains($style, 'top: 0') ||
                          str_contains($style, 'bottom:0') || str_contains($style, 'bottom: 0');

        $hasFullWidth = str_contains($style, 'width:100%') || str_contains($style, 'width: 100%') ||
                        str_contains($style, 'left:0') || str_contains($style, 'left: 0');

        if ($hasTopOrBottom && $hasFullWidth) {
            // Check if height or z-index is large
            return $this->createViolation(
                $element,
                "Fixed/sticky header or footer overlay may obscure focused items during keyboard navigation (WCAG 2.2 SC 2.4.11).",
                "Ensure html/body has adequate scroll-padding-top or scroll-padding-bottom to prevent focused elements from being hidden under sticky bars."
            );
        }

        return null;
    }
}
