<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaDeprecatedRoles extends AbstractRule
{
    /** @var array<string, string> */
    private const DEPRECATED_ROLES = [
        'directory' => 'Use role="list" or native <ul>/<ol> instead of deprecated role="directory".',
    ];

    /** @var string[] */
    private const ABSTRACT_ROLES = [
        'command', 'composite', 'input', 'landmark', 'range', 'roletype',
        'section', 'sectionhead', 'select', 'structure', 'widget', 'window',
    ];

    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_DEPRECATED_ROLE';
    }

    public function getDescription(): string
    {
        return 'Deprecated WAI-ARIA 1.2 roles (e.g. directory) and abstract ARIA ontology roles must not be used in markup.';
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
        if (!$element->hasAttribute('role')) {
            return null;
        }

        $roles = preg_split('/\s+/', strtolower(trim($element->getAttribute('role'))));
        if (!is_array($roles)) {
            return null;
        }

        foreach ($roles as $role) {
            if ($role === '') {
                continue;
            }
            if (isset(self::DEPRECATED_ROLES[$role])) {
                return $this->createViolation(
                    $element,
                    sprintf('ARIA role="%s" is deprecated in WAI-ARIA 1.2.', $role),
                    self::DEPRECATED_ROLES[$role]
                );
            }
            if (in_array($role, self::ABSTRACT_ROLES, true)) {
                return $this->createViolation(
                    $element,
                    sprintf('ARIA role="%s" is an abstract taxonomy role and must never be used directly by authors.', $role),
                    'Replace the abstract role with a concrete WAI-ARIA role or native semantic HTML element.'
                );
            }
        }

        return null;
    }
}
