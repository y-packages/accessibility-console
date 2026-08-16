<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaLabelMisuseOnGeneric extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_LABEL_GENERIC';
    }

    public function getDescription(): string
    {
        return 'aria-label or aria-labelledby should not be used on generic <div> or <span> elements without an ARIA role.';
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
        $tag = strtolower($element->tagName);
        if ($tag !== 'div' && $tag !== 'span') {
            return null;
        }

        $hasRole = $element->hasAttribute('role') && trim($element->getAttribute('role')) !== '';
        if ($hasRole) {
            return null;
        }

        $hasAriaLabel = $element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '';
        $hasAriaLabelledby = $element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '';

        if ($hasAriaLabel || $hasAriaLabelledby) {
            return $this->createViolation(
                $element,
                "Generic <{$tag}> uses aria-label/aria-labelledby without a semantic ARIA role (assistive technologies ignore names on generic elements).",
                "Add an appropriate ARIA role (e.g. role=\"region\", role=\"group\", role=\"navigation\") or use a semantic HTML5 element."
            );
        }

        return null;
    }
}
