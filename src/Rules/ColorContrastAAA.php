<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use Spatie\Color\Rgb;
use YakNet\AccessibilityConsole\Color\Contrast\ContrastRatioCalculator;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ColorContrastAAA extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_4_6_CONTRAST_AAA';
    }

    public function getDescription(): string
    {
        return 'Enhanced contrast (WCAG AAA) requires at least 7:1 contrast ratio for normal text and 4.5:1 for large text (WCAG 1.4.6).';
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

        $fg = $this->extractHex($style, 'color');
        $bg = $this->extractHex($style, 'background-color') ?? $this->extractHex($style, 'background');
        if ($fg === null || $bg === null) {
            return null;
        }

        $fgRgb = $this->hexToRgb($fg);
        $bgRgb = $this->hexToRgb($bg);
        if ($fgRgb === null || $bgRgb === null) {
            return null;
        }

        $ratio = ContrastRatioCalculator::calculate($fgRgb, $bgRgb);
        $isLarge = $this->isLargeText($style);
        $minRatio = $isLarge ? 4.5 : 7.0;

        if ($ratio < $minRatio) {
            return $this->createViolation(
                $element,
                sprintf('Contrast ratio %.2f:1 does not meet WCAG AAA enhanced threshold of %.1f:1 (WCAG 1.4.6).', $ratio, $minRatio),
                sprintf('Increase contrast between foreground (%s) and background (%s) to at least %.1f:1.', $fg, $bg, $minRatio)
            );
        }

        return null;
    }

    private function extractHex(string $style, string $property): ?string
    {
        if (!preg_match('/(?:^|;)\s*' . preg_quote($property, '/') . '\s*:\s*([^;]+)/i', $style, $m)) {
            return null;
        }
        $val = trim($m[1]);
        if (preg_match('/#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})\b/', $val, $hex)) {
            return '#' . $hex[1];
        }
        if (preg_match('/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $val, $rgb)) {
            return sprintf('#%02x%02x%02x', min(255, (int)$rgb[1]), min(255, (int)$rgb[2]), min(255, (int)$rgb[3]));
        }
        $named = [
            'black' => '#000000',
            'white' => '#ffffff',
            'gray' => '#808080',
            'grey' => '#808080',
            'silver' => '#c0c0c0',
            'red' => '#ff0000',
            'blue' => '#0000ff',
            'green' => '#008000',
            'yellow' => '#ffff00',
            'navy' => '#000080',
        ];
        $lower = strtolower($val);
        return $named[$lower] ?? null;
    }

    private function hexToRgb(string $hex): ?Rgb
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return null;
        }
        return new Rgb(
            (int)hexdec(substr($hex, 0, 2)),
            (int)hexdec(substr($hex, 2, 2)),
            (int)hexdec(substr($hex, 4, 2))
        );
    }

    private function isLargeText(string $style): bool
    {
        if (!preg_match('/font-size\s*:\s*([0-9.]+)(px|pt|rem|em)/i', $style, $m)) {
            return false;
        }
        $val = (float)$m[1];
        $unit = strtolower($m[2]);
        $isBold = (bool)preg_match('/font-weight\s*:\s*(?:bold|[7-9]00)/i', $style);

        if ($isBold) {
            return ($unit === 'px' && $val >= 18.66) || ($unit === 'pt' && $val >= 14) || (($unit === 'rem' || $unit === 'em') && $val >= 1.2);
        }
        return ($unit === 'px' && $val >= 24) || ($unit === 'pt' && $val >= 18) || (($unit === 'rem' || $unit === 'em') && $val >= 1.5);
    }
}
