<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class RadioGroupFieldset extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_RADIO_GROUP_FIELDSET'; }
    public function getDescription(): string { return 'Radio button groups sharing the same name should be grouped inside a <fieldset> with a <legend> or role="radiogroup".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        if (strtolower($element->getAttribute('type')) !== 'radio') {
            return null;
        }

        $name = $element->getAttribute('name');
        if ($name === '') {
            return null;
        }

        // Check if ancestor is fieldset or role="radiogroup"
        $current = $element->parentNode;
        while ($current instanceof DOMElement) {
            $parentTag = strtolower($current->tagName);
            $parentRole = strtolower($current->getAttribute('role'));

            if ($parentTag === 'fieldset') {
                $legends = $current->getElementsByTagName('legend');
                if ($legends->length > 0) {
                    return null;
                }
            }

            if ($parentRole === 'radiogroup' && ($current->hasAttribute('aria-label') || $current->hasAttribute('aria-labelledby'))) {
                return null;
            }

            $current = $current->parentNode;
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            sprintf('Wrap radio inputs named "%s" inside a <fieldset> with a <legend> to group related radio choices.', $name)
        );
    }
}
