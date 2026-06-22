<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class NoFocusOutline extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_7_FOCUS_OUTLINE'; }
    public function getDescription(): string { return 'Focus outlines should not be disabled in inline styles without providing custom focus styles.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 5; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('style')) {
            return null;
        }

        $style = strtolower($element->getAttribute('style'));
        
        // Match outline: none or outline: 0
        if (preg_match('/\boutline\s*:\s*(none|0)\b/', $style)) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Avoid disabling focus outlines using outline: none or outline: 0 in inline styles. Ensure keyboard users can see where focus is.'
            );
        }

        return null;
    }
}
