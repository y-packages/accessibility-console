<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InputMissingAutocomplete extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_5_INPUT_AUTOCOMPLETE'; }
    public function getDescription(): string { return 'Input fields collecting personal data should have an autocomplete attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        $type = strtolower($element->getAttribute('type'));
        if (in_array($type, ['hidden', 'submit', 'button', 'reset', 'checkbox', 'radio', 'image', 'file'], true)) {
            return null;
        }

        $name = strtolower($element->getAttribute('name'));
        $id = strtolower($element->getAttribute('id'));
        $targetKeywords = ['email', 'phone', 'tel', 'username', 'user', 'password', 'pass', 'address', 'zip', 'city', 'country', 'name', 'fname', 'lname'];

        $isPersonalField = false;
        if (in_array($type, ['email', 'tel'], true)) {
            $isPersonalField = true;
        } else {
            foreach ($targetKeywords as $keyword) {
                if (str_contains($name, $keyword) || str_contains($id, $keyword)) {
                    $isPersonalField = true;
                    break;
                }
            }
        }

        if ($isPersonalField && (!$element->hasAttribute('autocomplete') || trim($element->getAttribute('autocomplete')) === '')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add an autocomplete attribute (e.g., autocomplete="email", autocomplete="username") to improve accessibility and user experience.'
            );
        }

        return null;
    }
}
