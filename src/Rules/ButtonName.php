<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ButtonName extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_BUTTON'; }
    public function getDescription(): string { return 'Buttons must have discernible text.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'button') {
            return null;
        }

        $text = trim($element->textContent);
        if ($text === '' && !$element->hasAttribute('aria-label') && !$element->hasAttribute('title')) {
            return $this->createViolation($element, $this->getDescription(), 'Add text or an aria-label to the button.');
        }

        return null;
    }
}
