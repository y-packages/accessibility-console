<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ScrollableRegionFocusable extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_1_1_SCROLLABLE_FOCUS'; }
    public function getDescription(): string { return 'Scrollable regions must be focusable via keyboard using tabindex="0".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $style = $element->getAttribute('style');
        if ($style && preg_match('/overflow(-x|-y)?\s*:\s*(scroll|auto)/i', $style)) {
            if (!$element->hasAttribute('tabindex') || (int)$element->getAttribute('tabindex') < 0) {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Add tabindex="0" to the scrollable element so keyboard users can navigate to and scroll it.'
                );
            }
        }

        return null;
    }
}
