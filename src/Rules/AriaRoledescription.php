<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaRoledescription extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_ROLEDESCRIPTION';
    }

    public function getDescription(): string
    {
        return 'aria-roledescription must only be used on elements with a non-generic role and must not be empty.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('aria-roledescription')) {
            return null;
        }

        $desc = trim($element->getAttribute('aria-roledescription'));
        if ($desc === '') {
            return $this->createViolation(
                $element,
                "Attribute aria-roledescription is empty or contains only whitespace.",
                "Provide a meaningful description or remove the aria-roledescription attribute."
            );
        }

        $role = strtolower(trim($element->getAttribute('role')));
        if ($role === 'presentation' || $role === 'none' || $role === 'generic') {
            return $this->createViolation(
                $element,
                "aria-roledescription must not be used on elements with role=\"{$role}\".",
                "Remove aria-roledescription from presentation/generic elements, or provide an appropriate semantic ARIA role."
            );
        }

        $tagName = strtolower($element->tagName);
        if ($role === '' && in_array($tagName, ['div', 'span'], true)) {
            return $this->createViolation(
                $element,
                "aria-roledescription is used on a generic <{$tagName}> element without an explicit ARIA role.",
                "Add an explicit ARIA role (e.g. role=\"region\", role=\"button\") or use a semantic HTML element."
            );
        }

        return null;
    }
}
