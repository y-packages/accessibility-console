<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ColorContrast extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_4_3_CONTRAST'; }
    public function getDescription(): string { return 'Text must have sufficient contrast against the background (4.5:1 minimum).'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('style')) {
            return null;
        }

        $style = $element->getAttribute('style');
        
        $color = $this->parseColorValue($style, 'color');
        $bg = $this->parseColorValue($style, 'background-color') ?: $this->parseColorValue($style, 'background');

        if ($color === null || $bg === null) {
            return null;
        }

        $cRGB = $this->hexToRgb($color);
        $bgRGB = $this->hexToRgb($bg);

        if ($cRGB === null || $bgRGB === null) {
            return null;
        }

        $cLum = $this->getLuminance($cRGB[0], $cRGB[1], $cRGB[2]);
        $bgLum = $this->getLuminance($bgRGB[0], $bgRGB[1], $bgRGB[2]);

        $ratio = ($cLum > $bgLum) ? ($cLum + 0.05) / ($bgLum + 0.05) : ($bgLum + 0.05) / ($cLum + 0.05);

        $isLarge = false;
        if (preg_match('/font-size\s*:\s*([0-9.]+)(px|em|rem|pt)/i', $style, $matches)) {
            $val = (float)$matches[1];
            $unit = strtolower($matches[2]);
            if (($unit === 'px' && $val >= 24) || ($unit === 'pt' && $val >= 18) || (($unit === 'em' || $unit === 'rem') && $val >= 1.5)) {
                $isLarge = true;
            }
        }
        if (preg_match('/font-weight\s*:\s*(bold|[7-9]00)/i', $style)) {
            if (preg_match('/font-size\s*:\s*([0-9.]+)(px|em|rem|pt)/i', $style, $matches)) {
                $val = (float)$matches[1];
                $unit = strtolower($matches[2]);
                if (($unit === 'px' && $val >= 18) || ($unit === 'pt' && $val >= 14) || (($unit === 'em' || $unit === 'rem') && $val >= 1.2)) {
                    $isLarge = true;
                }
            }
        }

        $minRatio = $isLarge ? 3.0 : 4.5;

        if ($ratio < $minRatio) {
            $formattedRatio = round($ratio, 2);
            return $this->createViolation(
                $element,
                "Insufficient contrast ratio of {$formattedRatio}:1. Minimum required is {$minRatio}:1.",
                "Adjust colors to increase contrast. For example, text: {$color}, background: {$bg}."
            );
        }

        return null;
    }

    private function parseColorValue(string $style, string $property): ?string
    {
        if (preg_match('/(?:^|;)\s*' . preg_quote($property, '/') . '\s*:\s*([^;]+)/i', $style, $matches)) {
            $value = trim($matches[1]);
            if (preg_match('/^#([0-9a-f]{3,6})$/i', $value)) {
                return $value;
            }
            $colors = [
                'black' => '#000000', 'white' => '#ffffff', 'red' => '#ff0000',
                'green' => '#008000', 'blue' => '#0000ff', 'yellow' => '#ffff00',
                'gray' => '#808080', 'grey' => '#808080', 'silver' => '#c0c0c0',
            ];
            if (isset($colors[strtolower($value)])) {
                return $colors[strtolower($value)];
            }
        }
        return null;
    }

    private function hexToRgb(string $hex): ?array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            return [$r, $g, $b];
        } elseif (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return [$r, $g, $b];
        }
        return null;
    }

    private function getLuminance(float $r, float $g, float $b): float
    {
        $rs = $r / 255.0;
        $gs = $g / 255.0;
        $bs = $b / 255.0;

        $rVal = ($rs <= 0.03928) ? $rs / 12.92 : pow(($rs + 0.055) / 1.055, 2.4);
        $gVal = ($gs <= 0.03928) ? $gs / 12.92 : pow(($gs + 0.055) / 1.055, 2.4);
        $bVal = ($bs <= 0.03928) ? $bs / 12.92 : pow(($bs + 0.055) / 1.055, 2.4);

        return 0.2126 * $rVal + 0.7152 * $gVal + 0.0722 * $bVal;
    }
}
