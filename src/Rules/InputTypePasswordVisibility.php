<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InputTypePasswordVisibility extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_PASSWORD_VISIBILITY_TOGGLE'; }
    public function getDescription(): string { return 'Password visibility toggle buttons must have accessible names and specify aria-pressed or aria-label attributes.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'button') {
            return null;
        }

        $class = strtolower($element->getAttribute('class'));
        $id = strtolower($element->getAttribute('id'));
        $ariaLabel = $element->getAttribute('aria-label');

        $isPasswordToggle = str_contains($class, 'password') || str_contains($class, 'toggle-pass') || str_contains($id, 'password') || str_contains($id, 'toggle-pass');

        if ($isPasswordToggle && empty($ariaLabel) && !$element->hasAttribute('aria-labelledby') && empty(trim($element->textContent))) {
            return $this->createViolation(
                $element,
                'Password visibility toggle button is missing an accessible name.',
                'Add an aria-label="Şifreyi Göster/Gizle" or aria-pressed="false" attribute to the password toggle button.'
            );
        }

        return null;
    }
}
