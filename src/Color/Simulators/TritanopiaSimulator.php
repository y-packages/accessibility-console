<?php

namespace YakNet\AccessibilityConsole\Color\Simulators;

use Spatie\Color\Rgb;

class TritanopiaSimulator
{
    /**
     * Simulate Tritanopia (blue-blindness) vision deficiency for a given RGB color.
     */
    public static function simulate(Rgb $color): Rgb
    {
        $r = $color->red();
        $g = $color->green();
        $b = $color->blue();

        $simR = (int)round(0.95000 * $r + 0.05000 * $g);
        $simG = (int)round(0.00000 * $r + 0.43333 * $g + 0.56667 * $b);
        $simB = (int)round(0.00000 * $r + 0.47500 * $g + 0.52500 * $b);

        return new Rgb(
            min(255, max(0, $simR)),
            min(255, max(0, $simG)),
            min(255, max(0, $simB))
        );
    }
}
