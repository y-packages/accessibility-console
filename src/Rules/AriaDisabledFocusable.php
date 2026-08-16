<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaDisabledFocusable extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_DISABLED';
    }

    public function getDescription(): string
    {
        return 'Disabled elements should not have conflicting focusable tabindex settings.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        // 1. Native disabled element with explicit positive/zero tabindex
        if ($element->hasAttribute('disabled')) {
            if ($element->hasAttribute('tabindex')) {
                $tabindex = (int)$element->getAttribute('tabindex');
                if ($tabindex >= 0) {
                    return $this->createViolation(
                        $element,
                        "Element has both the 'disabled' attribute and a focusable tabindex=\"{$tabindex}\".",
                        "Remove the tabindex or set tabindex=\"-1\" to avoid keyboard navigation confusion on disabled elements."
                    );
                }
            }
        }

        // 2. Elements with aria-disabled="true" on non-native elements
        if (strtolower(trim($element->getAttribute('aria-disabled'))) === 'true') {
            $tagName = strtolower($element->tagName);
            if (in_array($tagName, ['a', 'button'], true) && $element->hasAttribute('tabindex')) {
                $tabindex = (int)$element->getAttribute('tabindex');
                if ($tabindex > 0) {
                    return $this->createViolation(
                        $element,
                        "Element has aria-disabled=\"true\" with positive tabindex=\"{$tabindex}\".",
                        "Avoid positive tabindex on disabled interactive elements."
                    );
                }
            }
        }

        return null;
    }
}
