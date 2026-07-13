<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AutocompleteValid extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_5_AUTOCOMPLETE'; }
    public function getDescription(): string { return 'Input fields that collect common user information must have a valid autocomplete attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        $type = strtolower($element->getAttribute('type') ?: 'text');
        if (in_array($type, ['hidden', 'submit', 'button', 'reset', 'file', 'checkbox', 'radio', 'image'], true)) {
            return null;
        }

        $name = strtolower($element->getAttribute('name'));
        $id = strtolower($element->getAttribute('id'));

        $personalFields = ['email', 'tel', 'phone', 'zip', 'postal', 'address', 'username', 'password', 'name', 'fname', 'lname', 'search'];
        $isPersonal = false;

        if ($type === 'email' || $type === 'tel' || $type === 'password') {
            $isPersonal = true;
        } else {
            foreach ($personalFields as $field) {
                if (str_contains($name, $field) || str_contains($id, $field)) {
                    $isPersonal = true;
                    break;
                }
            }
        }

        if ($isPersonal && !$element->hasAttribute('autocomplete')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add a valid autocomplete attribute (e.g., autocomplete="email" or autocomplete="current-password").'
            );
        }

        return null;
    }
}
