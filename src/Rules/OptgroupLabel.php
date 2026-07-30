<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class OptgroupLabel extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_OPTGROUP_LABEL'; }
    public function getDescription(): string { return '<optgroup> elements must have a non-empty label attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'optgroup') {
            return null;
        }

        if (!$element->hasAttribute('label') || trim($element->getAttribute('label')) === '') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add a descriptive label attribute to the <optgroup> element.'
            );
        }

        return null;
    }
}
