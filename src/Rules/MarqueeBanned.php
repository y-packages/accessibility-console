<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MarqueeBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_2_2_MARQUEE'; }
    public function getDescription(): string { return 'The marquee element is obsolete and presents significant accessibility barriers.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) === 'marquee') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the marquee tag and use CSS animations or transitions instead.'
            );
        }

        return null;
    }
}
