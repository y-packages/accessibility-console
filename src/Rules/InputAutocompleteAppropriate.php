<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InputAutocompleteAppropriate extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_5_AUTOCOMPLETE_APPROPRIATE';
    }

    public function getDescription(): string
    {
        return 'Input autocomplete value must be semantically appropriate for the input type (WCAG 1.3.5).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        if (!$element->hasAttribute('autocomplete')) {
            return null;
        }

        $type = strtolower($element->getAttribute('type') ?: 'text');
        $autocomplete = strtolower(trim($element->getAttribute('autocomplete')));

        if ($autocomplete === '' || in_array($autocomplete, ['on', 'off'], true)) {
            return null;
        }

        // Email type should not have telephone or postal autocomplete
        if ($type === 'email' && (str_contains($autocomplete, 'tel') || str_contains($autocomplete, 'postal') || str_contains($autocomplete, 'bday'))) {
            return $this->createViolation(
                $element,
                "Inappropriate autocomplete=\"{$autocomplete}\" on <input type=\"email\">.",
                "Use autocomplete=\"email\" or \"username\" for email inputs."
            );
        }

        // Number type should not have name, street-address, or email
        if ($type === 'number' && (str_contains($autocomplete, 'name') || str_contains($autocomplete, 'email') || str_contains($autocomplete, 'address') || str_contains($autocomplete, 'country'))) {
            return $this->createViolation(
                $element,
                "Inappropriate autocomplete=\"{$autocomplete}\" on <input type=\"number\">.",
                "Number inputs should use numeric autocomplete tokens like \"bday-year\", \"cc-exp-year\", or \"tel\"."
            );
        }

        // Checkbox or Radio should not have email or password autocompletes
        if (in_array($type, ['checkbox', 'radio'], true) && (str_contains($autocomplete, 'password') || str_contains($autocomplete, 'email'))) {
            return $this->createViolation(
                $element,
                "Inappropriate autocomplete=\"{$autocomplete}\" on <input type=\"{$type}\">.",
                "Do not apply credential or text autocompletes to checkbox/radio inputs."
            );
        }

        return null;
    }
}
