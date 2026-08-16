<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaRedundantRole extends AbstractRule
{
    /** @var array<string, string> */
    private static array $redundantMap = [
        'nav' => 'navigation',
        'main' => 'main',
        'button' => 'button',
        'article' => 'article',
        'aside' => 'complementary',
        'dialog' => 'dialog',
        'figure' => 'figure',
    ];

    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_REDUNDANT_ROLE';
    }

    public function getDescription(): string
    {
        return 'Avoid specifying redundant ARIA roles on HTML elements with identical implicit semantics.';
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
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('role')) {
            return null;
        }

        $tagName = strtolower($element->tagName);
        $role = strtolower(trim($element->getAttribute('role')));

        if (isset(self::$redundantMap[$tagName]) && self::$redundantMap[$tagName] === $role) {
            return $this->createViolation(
                $element,
                "Redundant ARIA role=\"{$role}\" on <{$tagName}>. The element natively possesses this implicit semantic role.",
                "Remove the redundant role=\"{$role}\" attribute (First Rule of ARIA)."
            );
        }

        return null;
    }
}
