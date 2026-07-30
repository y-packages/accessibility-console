<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableNestedBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_TABLE_NESTED_BANNED'; }
    public function getDescription(): string { return 'Tables should not be nested inside another table as it confuses screen reader data table navigation.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'table') {
            return null;
        }

        $parent = $element->parentNode;
        while ($parent instanceof DOMElement) {
            if (strtolower($parent->tagName) === 'table') {
                return $this->createViolation(
                    $element,
                    'Table element is nested inside another table.',
                    'Refactor layout to use modern CSS Flexbox/Grid instead of nested tables for tabular data.'
                );
            }
            $parent = $parent->parentNode;
        }

        return null;
    }
}
