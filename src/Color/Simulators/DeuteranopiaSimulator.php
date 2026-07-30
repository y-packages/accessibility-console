<?php

namespace YakNet\AccessibilityConsole\Color\Simulators;

use Spatie\Color\Rgb;

class DeuteranopiaSimulator
{
    /**
     * Simulate Deuteranopia (green-blindness) vision deficiency for a given RGB color.
     */
    public static function simulate(Rgb $color): Rgb
    {
        $r = $color->red();
        $g = $color->green();
        $b = $color->blue();

        $simR = (int)round(0.625 * $r + 0.375 * $g);
        $simG = (int)round(0.700 * $r + 0.300 * $g);
        $simB = (int)round(0.000 * $r + 0.300 * $g + 0.700 * $b);

        return new Rgb(
            min(255, max(0, $simR)),
            min(255, max(0, $simG)),
            min(255, max(0, $simB))
        );
    }
}
