<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SectionAccessibleName extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_SECTION_NAME';
    }

    public function getDescription(): string
    {
        return 'Elements with role="region" must have an accessible name (aria-label or aria-labelledby) to be exposed as a landmark.';
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
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower(trim($element->getAttribute('role'))) !== 'region') {
            return null;
        }

        $hasName = ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '')
            || ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '')
            || ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '');

        if (!$hasName) {
            return $this->createViolation(
                $element,
                'Element with role="region" is missing an accessible name (aria-label or aria-labelledby) and will not function effectively as a landmark.',
                'Add aria-label="<Region Purpose>" or aria-labelledby="<heading-id>" to the region landmark.'
            );
        }

        return null;
    }
}
