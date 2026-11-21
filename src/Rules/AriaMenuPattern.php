<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaMenuPattern extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_MENU';
    }

    public function getDescription(): string
    {
        return 'Elements with role="menu" or role="menubar" must contain valid menuitem roles and avoid mixing bare links without menuitem semantics.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        $role = strtolower(trim($element->getAttribute('role')));
        if (!in_array($role, ['menu', 'menubar'], true)) {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        $xpath = new DOMXPath($doc);
        $menuItems = $xpath->query('.//*[@role="menuitem" or @role="menuitemcheckbox" or @role="menuitemradio"]', $element);

        if ($menuItems !== false && $menuItems->length === 0) {
            return $this->createViolation(
                $element,
                sprintf('Container with role="%s" does not contain any role="menuitem", "menuitemcheckbox", or "menuitemradio" descendants.', $role),
                'For site navigation links, use <nav><ul><li><a> instead of role="menu". For application menus, assign role="menuitem" to each action.'
            );
        }

        return null;
    }
}
