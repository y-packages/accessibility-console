<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class IframeSandboxAccessibility extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_IFRAME_TABINDEX';
    }

    public function getDescription(): string
    {
        return 'Visible <iframe> elements must not set tabindex="-1", which blocks keyboard users from tabbing into embedded content.';
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
        if (strtolower($element->tagName) !== 'iframe') {
            return null;
        }

        if (strtolower(trim($element->getAttribute('aria-hidden'))) === 'true' || $element->hasAttribute('hidden')) {
            return null;
        }

        if ($element->hasAttribute('tabindex') && (int)$element->getAttribute('tabindex') < 0) {
            return $this->createViolation(
                $element,
                'Visible <iframe> has negative tabindex="-1", preventing keyboard-only users from reaching interactive controls inside the frame.',
                'Remove tabindex="-1" from the <iframe>, or hide decorative frames with aria-hidden="true".'
            );
        }

        return null;
    }
}
