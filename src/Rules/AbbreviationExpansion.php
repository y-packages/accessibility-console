<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AbbreviationExpansion extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_1_4_ABBR_TITLE';
    }

    public function getDescription(): string
    {
        return '<abbr> elements must include a non-empty title attribute providing the expanded form of the abbreviation (WCAG 3.1.4).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AAA;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 5;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'abbr') {
            return null;
        }

        $title = trim($element->getAttribute('title'));
        if ($title === '') {
            return $this->createViolation(
                $element,
                '<abbr> element is missing a non-empty title attribute to explain the abbreviation (WCAG 3.1.4 Abbreviations).',
                'Add a title attribute with the full expansion (e.g., <abbr title="Web Content Accessibility Guidelines">WCAG</abbr>).'
            );
        }

        return null;
    }
}
