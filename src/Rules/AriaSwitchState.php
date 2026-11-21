<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaSwitchState extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_SWITCH';
    }

    public function getDescription(): string
    {
        return 'Elements with role="switch" must declare aria-checked ("true" or "false") and an accessible name (WAI-ARIA 1.2).';
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
        if (strtolower(trim($element->getAttribute('role'))) !== 'switch') {
            return null;
        }

        $tag = strtolower($element->tagName);
        $isNativeCheckbox = $tag === 'input' && strtolower($element->getAttribute('type')) === 'checkbox';

        if (!$isNativeCheckbox) {
            $checkedVal = strtolower(trim($element->getAttribute('aria-checked')));
            if (!in_array($checkedVal, ['true', 'false'], true)) {
                return $this->createViolation(
                    $element,
                    'Element with role="switch" must specify aria-checked="true" or aria-checked="false" (note: "mixed" is invalid for switches).',
                    'Add aria-checked="false" (or "true") and toggle its value programmatically when activated.'
                );
            }
        }

        $hasName = trim($element->textContent) !== ''
            || ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '')
            || ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '')
            || ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '');

        if (!$hasName && !$isNativeCheckbox) {
            return $this->createViolation(
                $element,
                'Element with role="switch" is missing an accessible name.',
                'Add visible label text, aria-label, or aria-labelledby to identify what the switch controls.'
            );
        }

        return null;
    }
}
