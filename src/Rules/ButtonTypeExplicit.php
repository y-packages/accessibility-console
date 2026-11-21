<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ButtonTypeExplicit extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_2_2_BUTTON_TYPE';
    }

    public function getDescription(): string
    {
        return '<button> elements inside a <form> should explicitly declare type="submit", type="button", or type="reset" to prevent unexpected submissions (WCAG 3.2.2).';
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
        if (strtolower($element->tagName) !== 'button') {
            return null;
        }

        if ($element->hasAttribute('type')) {
            $type = strtolower(trim($element->getAttribute('type')));
            if (in_array($type, ['submit', 'button', 'reset'], true)) {
                return null;
            }
        }

        // Only flag buttons located inside a <form>
        $insideForm = false;
        $parent = $element->parentNode;
        while ($parent instanceof DOMElement) {
            if (strtolower($parent->tagName) === 'form') {
                $insideForm = true;
                break;
            }
            $parent = $parent->parentNode;
        }

        if (!$insideForm) {
            return null;
        }

        return $this->createViolation(
            $element,
            '<button> inside a <form> omits an explicit type attribute and defaults to type="submit", risking unintended form submission (WCAG 3.2.2).',
            'Explicitly add type="button" for non-submitting actions or type="submit" for form submission.'
        );
    }
}
