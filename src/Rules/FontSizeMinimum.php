<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FontSizeMinimum extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_4_4_FONT_SIZE_MIN';
    }

    public function getDescription(): string
    {
        return 'Inline text font sizes should not be smaller than 11px (or 0.7rem) to maintain readability for low-vision users (WCAG 1.4.4).';
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
        $style = $element->getAttribute('style');
        if ($style === '' || trim($element->textContent) === '') {
            return null;
        }

        if (preg_match('/(?:^|;)\s*font-size\s*:\s*([0-9.]+)(px|pt|rem|em)\b/i', $style, $m)) {
            $value = (float)$m[1];
            $unit = strtolower($m[2]);

            $tooSmall = ($unit === 'px' && $value > 0 && $value < 11.0)
                || ($unit === 'pt' && $value > 0 && $value < 8.5)
                || (($unit === 'rem' || $unit === 'em') && $value > 0 && $value < 0.7);

            if ($tooSmall) {
                return $this->createViolation(
                    $element,
                    sprintf('Element sets an excessively small inline font-size of %s%s, impeding readability for low-vision users.', $m[1], $unit),
                    'Use relative units (rem/em) with a readable base size (at least 14px-16px / 0.875rem-1rem for body copy).'
                );
            }
        }

        return null;
    }
}
