<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SummaryParent extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_SUMMARY_PARENT'; }
    public function getDescription(): string { return '<summary> elements must be direct children of a <details> element.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'summary') {
            return null;
        }

        $parent = $element->parentNode;
        if (!$parent instanceof DOMElement || strtolower($parent->tagName) !== 'details') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Wrap the <summary> element inside a <details> element or change it to a standard heading/button.'
            );
        }

        return null;
    }
}
