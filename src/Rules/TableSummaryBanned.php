<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableSummaryBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_TABLE_SUMMARY'; }
    public function getDescription(): string { return 'The summary attribute on table elements is obsolete and should not be used.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'table') {
            return null;
        }

        if ($element->hasAttribute('summary')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the summary attribute. Use the <caption> element or aria-describedby instead.'
            );
        }

        return null;
    }
}
