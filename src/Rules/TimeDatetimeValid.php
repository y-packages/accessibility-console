<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TimeDatetimeValid extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_TIME_DATETIME';
    }

    public function getDescription(): string
    {
        return '<time> elements specifying a datetime attribute must use valid machine-readable ISO-8601 date, time, or duration syntax.';
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
        if (strtolower($element->tagName) !== 'time' || !$element->hasAttribute('datetime')) {
            return null;
        }

        $dt = trim($element->getAttribute('datetime'));
        if ($dt === '' || preg_match('/\{\{|\{%|<\?php/', $dt)) {
            return null;
        }

        // Valid HTML5 <time datetime="..."> formats:
        // YYYY, YYYY-MM, YYYY-MM-DD, MM-DD, HH:MM(:SS(.sss))?, YYYY-MM-DD[T ]HH:MM(:SS)?(Z|±HH:MM)?, YYYY-Www, P... duration
        $pattern = '/^('
            . '\d{4}(?:-\d{2}(?:-\d{2})?)?(?:[T ]\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:?\d{2})?)?'
            . '|\d{2}-\d{2}'
            . '|\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?'
            . '|\d{4}-W\d{2}'
            . '|P(?:\d+[YMWD])*(?:T(?:\d+[HMS])+)?'
            . '|[+-]\d{2}:?\d{2}|Z'
            . ')$/';

        if (!preg_match($pattern, $dt)) {
            return $this->createViolation(
                $element,
                sprintf('<time> element has invalid machine-readable datetime="%s".', $dt),
                'Use a valid ISO-8601 value in datetime (e.g., datetime="2026-11-21" or datetime="2026-11-21T14:30:00Z").'
            );
        }

        return null;
    }
}
