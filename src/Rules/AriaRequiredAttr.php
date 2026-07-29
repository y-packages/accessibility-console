<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaRequiredAttr extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ARIA_REQUIRED_ATTR'; }
    public function getDescription(): string { return 'Elements with ARIA roles must have all required ARIA attributes for that role.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('role')) {
            return null;
        }

        $roles = explode(' ', strtolower(trim($element->getAttribute('role'))));
        $requiredMap = [
            'slider' => ['aria-valuenow', 'aria-valuemin', 'aria-valuemax'],
            'checkbox' => ['aria-checked'],
            'combobox' => ['aria-expanded'],
            'scrollbar' => ['aria-controls', 'aria-valuenow', 'aria-valuemax', 'aria-valuemin', 'aria-orientation'],
            'spinbutton' => ['aria-valuenow', 'aria-valuemax', 'aria-valuemin'],
            'switch' => ['aria-checked'],
            'meter' => ['aria-valuenow'],
            'separator' => ['aria-valuenow'] // strictly when focusable, but we check if the role is present
        ];

        foreach ($roles as $role) {
            if (isset($requiredMap[$role])) {
                $missingAttrs = [];
                foreach ($requiredMap[$role] as $reqAttr) {
                    if (!$element->hasAttribute($reqAttr)) {
                        $missingAttrs[] = $reqAttr;
                    }
                }

                if (!empty($missingAttrs)) {
                    // Specific case: separator without tabindex is not focusable and doesn't require aria-valuenow in some contexts
                    if ($role === 'separator' && !$element->hasAttribute('tabindex')) {
                        continue;
                    }

                    $missingStr = implode(', ', $missingAttrs);
                    return $this->createViolation(
                        $element,
                        "ARIA role '{$role}' eksik zorunlu niteliklere sahip: {$missingStr}.",
                        "Belirtilen ARIA rolü için zorunlu olan {$missingStr} niteliklerini ekleyin."
                    );
                }
            }
        }

        return null;
    }
}
