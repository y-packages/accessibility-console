<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableHeaders extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_TABLE'; }
    public function getDescription(): string { return 'Data tables must have headers (th) or captions for screen readers.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'table') {
            return null;
        }

        // Skip layout tables explicitly marked as presentation
        $role = strtolower($element->getAttribute('role'));
        if ($role === 'presentation' || $role === 'none') {
            return null;
        }

        // Check if there is at least one <th> or <caption>
        $thCount = $element->getElementsByTagName('th')->length;
        $captionCount = $element->getElementsByTagName('caption')->length;

        if ($thCount === 0 && $captionCount === 0) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add <th> header elements or a <caption> to the table, or set role="presentation" if it is only used for layout.'
            );
        }

        return null;
    }
}
