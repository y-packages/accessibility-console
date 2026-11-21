<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaSliderAttributes extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_SLIDER';
    }

    public function getDescription(): string
    {
        return 'Custom elements with role="slider" must provide aria-valuenow, aria-valuemin, aria-valuemax, and an accessible name.';
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
        if (strtolower(trim($element->getAttribute('role'))) !== 'slider') {
            return null;
        }

        if (strtolower($element->tagName) === 'input' && strtolower($element->getAttribute('type')) === 'range') {
            return null;
        }

        $missing = [];
        foreach (['aria-valuenow', 'aria-valuemin', 'aria-valuemax'] as $attr) {
            if (!$element->hasAttribute($attr) || !is_numeric(trim($element->getAttribute($attr)))) {
                $missing[] = $attr;
            }
        }

        $hasName = ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '')
            || ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '')
            || ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '');

        if (!$hasName) {
            $missing[] = 'accessible name (aria-label or aria-labelledby)';
        }

        if (!empty($missing)) {
            return $this->createViolation(
                $element,
                'Custom slider (role="slider") is missing required attributes: ' . implode(', ', $missing) . '.',
                'Specify numeric aria-valuemin, aria-valuemax, aria-valuenow, tabindex="0", and an aria-label, or use native <input type="range">.'
            );
        }

        return null;
    }
}
