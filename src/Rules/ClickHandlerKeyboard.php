<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ClickHandlerKeyboard extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_1_1_CLICK_KEYBOARD'; }
    public function getDescription(): string { return 'Non-interactive elements with click handlers should also have keyboard handlers.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('onclick')) {
            return null;
        }
        
        $tagName = strtolower($element->tagName);
        $interactiveElements = ['a', 'button', 'input', 'select', 'textarea', 'summary', 'details'];
        if (in_array($tagName, $interactiveElements)) {
            return null;
        }

        $role = strtolower($element->getAttribute('role'));
        if (($role === 'button' || $role === 'link') && $element->hasAttribute('tabindex')) {
            return null;
        }

        if ($element->hasAttribute('onkeydown') || $element->hasAttribute('onkeyup') || $element->hasAttribute('onkeypress')) {
            return null;
        }

        return $this->createViolation(
            $element,
            'onclick olay işleyicisine sahip etkileşimli olmayan öğenin klavye işleyicisi (onkeydown vb.) eksik. / Non-interactive element with onclick is missing a keyboard handler.',
            'Klavye desteği için onkeydown/onkeyup ekleyin ve tabindex="0" tanımlayın. / Add onkeydown/onkeyup handlers and tabindex="0" for keyboard support.'
        );
    }
}
