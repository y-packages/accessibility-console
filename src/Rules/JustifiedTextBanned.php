<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class JustifiedTextBanned extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_4_8_TEXT_JUSTIFY';
    }

    public function getDescription(): string
    {
        return 'Avoid full text justification (text-align: justify) on prose blocks, which creates uneven word spacing for readers with dyslexia (WCAG 1.4.8).';
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
        $style = $element->getAttribute('style');
        if ($style === '') {
            return null;
        }

        if (preg_match('/(?:^|;)\s*text-align\s*:\s*justify\b/i', $style)) {
            return $this->createViolation(
                $element,
                'Element uses text-align: justify, which creates irregular spacing ("rivers of white") impeding cognitive and dyslexic readability (WCAG 1.4.8).',
                'Use text-align: start (or left/right matching text direction) instead of text-align: justify.'
            );
        }

        return null;
    }
}
