<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaPressedToggle extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_TOGGLE_BUTTON_STATE'; }
    public function getDescription(): string { return 'Toggle buttons must use aria-pressed="true" or "false" to communicate state changes.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $tagName = strtolower($element->tagName);
        $role = strtolower($element->getAttribute('role'));

        if ($tagName !== 'button' && $role !== 'button') {
            return null;
        }

        $class = strtolower($element->getAttribute('class'));
        $dataToggle = strtolower($element->getAttribute('data-toggle'));

        $isToggleButton = str_contains($class, 'btn-toggle') || str_contains($class, 'toggle-btn') || $dataToggle === 'button';

        if (!$isToggleButton) {
            return null;
        }

        if ($element->hasAttribute('aria-pressed')) {
            $val = strtolower($element->getAttribute('aria-pressed'));
            if (in_array($val, ['true', 'false', 'mixed'], true)) {
                return null;
            }
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add aria-pressed="true" or aria-pressed="false" to indicate the toggle state to assistive technology.'
        );
    }
}
