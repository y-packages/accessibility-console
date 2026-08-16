<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class IframeTitle extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_IFRAME_TITLE';
    }

    public function getDescription(): string
    {
        return 'Iframe elements must have an accessible name (title or aria-label) to describe their content.';
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
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'iframe') {
            return null;
        }

        // Check if decorative / hidden
        if ($element->getAttribute('aria-hidden') === 'true') {
            return null;
        }

        $title = trim($element->getAttribute('title'));
        $ariaLabel = trim($element->getAttribute('aria-label'));
        $ariaLabelledby = trim($element->getAttribute('aria-labelledby'));

        if ($title !== '' || $ariaLabel !== '' || $ariaLabelledby !== '') {
            return null;
        }

        return $this->createViolation(
            $element,
            "Iframe element is missing an accessible title or aria-label attribute.",
            "Add a descriptive title attribute, e.g., title=\"Embedded Map\" or an aria-label."
        );
    }
}
