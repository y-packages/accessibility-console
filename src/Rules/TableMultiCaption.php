<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableMultiCaption extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_TABLE_MULTI_CAPTION'; }
    public function getDescription(): string { return 'A <table> element must not contain more than one <caption> element.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'table') {
            return null;
        }

        $captionCount = 0;
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'caption') {
                $captionCount++;
            }
        }

        if ($captionCount > 1) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove duplicate <caption> elements so that the table has at most one caption.'
            );
        }

        return null;
    }
}
