<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaComboboxPattern extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_COMBOBOX';
    }

    public function getDescription(): string
    {
        return 'Elements with role="combobox" must specify aria-expanded, aria-controls (or aria-owns), and an accessible name (WAI-ARIA 1.2).';
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
        if (strtolower(trim($element->getAttribute('role'))) !== 'combobox') {
            return null;
        }

        $missing = [];

        if (!$element->hasAttribute('aria-expanded') || !in_array(strtolower(trim($element->getAttribute('aria-expanded'))), ['true', 'false'], true)) {
            $missing[] = 'aria-expanded="true|false"';
        }

        $hasPopupRef = ($element->hasAttribute('aria-controls') && trim($element->getAttribute('aria-controls')) !== '')
            || ($element->hasAttribute('aria-owns') && trim($element->getAttribute('aria-owns')) !== '');
        if (!$hasPopupRef) {
            $missing[] = 'aria-controls="<popup-id>"';
        }

        $hasName = ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '')
            || ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '')
            || ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '')
            || ($element->hasAttribute('id') && trim($element->getAttribute('id')) !== '');
        if (!$hasName) {
            $missing[] = 'accessible name (aria-label / aria-labelledby / associated <label>)';
        }

        if (!empty($missing)) {
            return $this->createViolation(
                $element,
                'Combobox widget (role="combobox") is missing required WAI-ARIA 1.2 properties: ' . implode(', ', $missing) . '.',
                'Add aria-expanded="false", aria-controls="<listbox-id>", and an accessible name to the combobox element.'
            );
        }

        return null;
    }
}
