<?php

namespace YakNet\AccessibilityConsole\Color\Contrast;

use Spatie\Color\Rgb;

class ContrastRatioCalculator
{
    /**
     * Calculate WCAG 2.1 relative contrast ratio between foreground and background colors.
     */
    public static function calculate(Rgb $fg, Rgb $bg): float
    {
        $l1 = self::getRelativeLuminance($fg);
        $l2 = self::getRelativeLuminance($bg);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    public static function getRelativeLuminance(Rgb $color): float
    {
        $r = $color->red() / 255.0;
        $g = $color->green() / 255.0;
        $b = $color->blue() / 255.0;

        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
