<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FormErrorSummary extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_3_1_FORM_ERROR_SUMMARY';
    }

    public function getDescription(): string
    {
        return 'Form fields marked with aria-invalid="true" must programmatically reference an error description via aria-errormessage or aria-describedby (WCAG 3.3.1).';
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
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['input', 'select', 'textarea'], true)) {
            return null;
        }

        if (strtolower(trim($element->getAttribute('aria-invalid'))) !== 'true') {
            return null;
        }

        $hasErrorRef = ($element->hasAttribute('aria-errormessage') && trim($element->getAttribute('aria-errormessage')) !== '')
            || ($element->hasAttribute('aria-describedby') && trim($element->getAttribute('aria-describedby')) !== '');

        if (!$hasErrorRef) {
            return $this->createViolation(
                $element,
                'Form control has aria-invalid="true" without referencing an error message via aria-describedby or aria-errormessage (WCAG 3.3.1 Error Identification).',
                'Link the field to its visible error message using aria-describedby="field-error-id" or aria-errormessage="field-error-id".'
            );
        }

        return null;
    }
}
