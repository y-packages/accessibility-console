<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ScopeOnlyOnHeaders extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_TABLE_SCOPE'; }
    public function getDescription(): string { return 'The scope attribute is only valid on th elements, not td elements.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'td') {
            return null;
        }

        if ($element->hasAttribute('scope')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the scope attribute from the td element or change the element to th if it is a header.'
            );
        }

        return null;
    }
}
