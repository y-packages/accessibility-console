<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InteractiveRoleTabindex extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_1_1_ROLE_TABINDEX'; }
    public function getDescription(): string { return 'Elements with interactive ARIA roles must be focusable.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $role = strtolower($element->getAttribute('role'));
        $interactiveRoles = ['button', 'link', 'checkbox', 'menuitem', 'menuitemcheckbox', 'menuitemradio', 'option', 'radio', 'searchbox', 'slider', 'spinbutton', 'switch', 'tab', 'textbox', 'combobox', 'gridcell'];
        
        if (empty($role) || !in_array($role, $interactiveRoles)) {
            return null;
        }
        
        if ($element->hasAttribute('tabindex')) {
            return null;
        }
        
        $tagName = strtolower($element->tagName);
        $focusableNative = ['button', 'input', 'select', 'textarea', 'summary', 'details'];
        if (in_array($tagName, $focusableNative)) {
            return null;
        }
        
        if ($tagName === 'a' && $element->hasAttribute('href')) {
            return null;
        }
        if (($tagName === 'audio' || $tagName === 'video') && $element->hasAttribute('controls')) {
            return null;
        }

        return $this->createViolation(
            $element,
            'Etkileşimli bir role ('.$role.') sahip olan öğe klavye ile odaklanılabilir (focusable) değil. / Element with interactive role ('.$role.') is not focusable.',
            'Öğeye tabindex="0" (veya uygun tabindex değeri) ekleyerek odaklanılabilir hale getirin. / Add tabindex="0" to make the element focusable.'
        );
    }
}
