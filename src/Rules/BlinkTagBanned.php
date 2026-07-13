<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class BlinkTagBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_2_2_BLINK'; }
    public function getDescription(): string { return 'The <blink> tag is obsolete and should not be used as it causes text to flash/blink.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) === 'blink') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the <blink> tag and use CSS styling/animations if needed, or preferably avoid blinking content.'
            );
        }

        return null;
    }
}
