<?php

namespace YakNet\AccessibilityConsole\Badges;

/**
 * Options DTO for customizing SVG badge generation.
 */
class BadgeOptions
{
    /**
     * @param string $label Default left container text label
     * @param string $leftColor Hex color code for left container background
     * @param string|null $rightColor Optional hex color override for right container background
     * @param int $borderRadius Corner radius for SVG clipPath
     * @param string $fontFamily Font family CSS rule for badge typography
     * @param int $fontSize Font size in pixels
     */
    public function __construct(
        public readonly string $label = 'accessibility',
        public readonly string $leftColor = '#1f2937',
        public readonly ?string $rightColor = null,
        public readonly int $borderRadius = 3,
        public readonly string $fontFamily = 'Verdana,Geneva,DejaVu Sans,sans-serif',
        public readonly int $fontSize = 11,
    ) {
    }
}
