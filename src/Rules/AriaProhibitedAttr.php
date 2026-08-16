<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaProhibitedAttr extends AbstractRule
{
    /** @var array<string, array<int, string>> */
    private static array $prohibitedAttributesByRole = [
        'presentation' => ['aria-label', 'aria-labelledby', 'aria-describedby', 'aria-details'],
        'none' => ['aria-label', 'aria-labelledby', 'aria-describedby', 'aria-details'],
        'generic' => ['aria-label', 'aria-labelledby', 'aria-roledescription'],
        'caption' => ['aria-label', 'aria-labelledby']
    ];

    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_PROHIBITED_ATTR';
    }

    public function getDescription(): string
    {
        return 'Prohibited ARIA attributes must not be used on roles that do not support naming (e.g. presentation, none).';
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
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('role')) {
            return null;
        }

        $roles = explode(' ', strtolower(trim($element->getAttribute('role'))));

        foreach ($roles as $role) {
            if (isset(self::$prohibitedAttributesByRole[$role])) {
                $prohibited = self::$prohibitedAttributesByRole[$role];
                $foundProhibited = [];

                foreach ($prohibited as $attr) {
                    if ($element->hasAttribute($attr)) {
                        $foundProhibited[] = $attr;
                    }
                }

                if (!empty($foundProhibited)) {
                    $attrList = implode(', ', $foundProhibited);
                    return $this->createViolation(
                        $element,
                        "Element with role=\"{$role}\" contains prohibited ARIA attribute(s): {$attrList}.",
                        "Remove the prohibited attribute(s) or change the element's ARIA role to one that supports naming."
                    );
                }
            }
        }

        return null;
    }
}
