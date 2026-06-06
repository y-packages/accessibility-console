<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaFocusable extends AbstractRule
{
    /** @var array<int, string> */
    private static array $interactiveRoles = [
        'button', 'link', 'checkbox', 'menuitem', 'radio', 'textbox',
        'searchbox', 'slider', 'spinbutton', 'switch', 'tab'
    ];

    /** @var array<int, string> */
    private static array $nativeFocusable = [
        'a', 'button', 'input', 'select', 'textarea', 'iframe'
    ];

    public function getId(): string { return 'WCAG_2_1_1_ARIA_FOCUSABLE'; }
    public function getDescription(): string { return 'Elements with interactive ARIA roles must be focusable (e.g. have a tabindex attribute).'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('role')) {
            return null;
        }

        $role = strtolower(trim($element->getAttribute('role')));
        if (!in_array($role, self::$interactiveRoles, true)) {
            return null;
        }

        $tagName = strtolower($element->tagName);
        if (in_array($tagName, self::$nativeFocusable, true)) {
            return null;
        }

        if (!$element->hasAttribute('tabindex')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add tabindex="0" to make the element focusable via keyboard navigation.'
            );
        }

        return null;
    }
}
