<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InputTypeButtonDiscernible extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_INPUT_BUTTON'; }
    public function getDescription(): string { return 'Input buttons (button, submit, reset) must have discernible text.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        $type = strtolower($element->getAttribute('type'));
        if (in_array($type, ['button', 'submit', 'reset'], true)) {
            $value = trim($element->getAttribute('value'));
            $ariaLabel = trim($element->getAttribute('aria-label'));
            $title = trim($element->getAttribute('title'));

            if ($value === '' && $ariaLabel === '' && $title === '') {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Add a non-empty value, aria-label, or title attribute to the input button.'
                );
            }
        }

        return null;
    }
}
