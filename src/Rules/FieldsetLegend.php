<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FieldsetLegend extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_FIELDSET'; }
    public function getDescription(): string { return 'Fieldsets should have a legend.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'fieldset') {
            return null;
        }

        $legends = $element->getElementsByTagName('legend');
        if ($legends->length === 0) {
            return $this->createViolation($element, $this->getDescription(), 'Add a <legend> inside the fieldset.');
        }

        return null;
    }
}
