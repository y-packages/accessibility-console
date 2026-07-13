<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AutofocusBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_3_AUTOFOCUS'; }
    public function getDescription(): string { return 'Form controls should not use the autofocus attribute as it disorients screen reader and keyboard users.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if ($element->hasAttribute('autofocus')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the autofocus attribute and let the user navigate to the form field naturally, or use script-based focus with care.'
            );
        }

        return null;
    }
}
