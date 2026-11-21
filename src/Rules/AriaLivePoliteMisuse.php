<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaLivePoliteMisuse extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_3_ARIA_LIVE_ASSERTIVE';
    }

    public function getDescription(): string
    {
        return 'Avoid applying aria-live="assertive" on major page landmarks (<main>, <nav>, <header>, <footer>, <article>, <form>) as it aggressively interrupts screen readers.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
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
        if (strtolower(trim($element->getAttribute('aria-live'))) !== 'assertive') {
            return null;
        }

        $tag = strtolower($element->tagName);
        $landmarkTags = ['body', 'main', 'nav', 'header', 'footer', 'article', 'aside', 'form', 'table'];

        if (in_array($tag, $landmarkTags, true)) {
            return $this->createViolation(
                $element,
                sprintf('Landmark/container <%s> uses aria-live="assertive", which interrupts ongoing screen reader speech whenever any descendant updates.', $tag),
                'Reserve aria-live="assertive" strictly for urgent error/alert banners, and use aria-live="polite" on dedicated small status containers.'
            );
        }

        return null;
    }
}
