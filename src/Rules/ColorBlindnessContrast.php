<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use Spatie\Color\Hex;
use YakNet\AccessibilityConsole\Color\Contrast\ContrastRatioCalculator;
use YakNet\AccessibilityConsole\Color\Simulators\DeuteranopiaSimulator;
use YakNet\AccessibilityConsole\Color\Simulators\ProtanopiaSimulator;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ColorBlindnessContrast extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_4_1_COLOR_BLINDNESS_CONTRAST'; }
    public function getDescription(): string { return 'Text and background color contrast must remain accessible (minimum 4.5:1) under color vision deficiency simulations (Deuteranopia, Protanopia).'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('style')) {
            return null;
        }

        $style = $element->getAttribute('style');
        $colorHex = $this->extractColorFromStyle($style, 'color');
        $bgHex = $this->extractColorFromStyle($style, 'background-color') ?? $this->extractColorFromStyle($style, 'background');

        if (!$colorHex || !$bgHex) {
            return null;
        }

        try {
            $fgRgb = Hex::fromString($colorHex)->toRgb();
            $bgRgb = Hex::fromString($bgHex)->toRgb();
        } catch (\Throwable) {
            return null;
        }

        // Simulate Deuteranopia & Protanopia using dedicated simulators
        $simulations = [
            'Deuteranopia' => [
                'fg' => DeuteranopiaSimulator::simulate($fgRgb),
                'bg' => DeuteranopiaSimulator::simulate($bgRgb),
            ],
            'Protanopia' => [
                'fg' => ProtanopiaSimulator::simulate($fgRgb),
                'bg' => ProtanopiaSimulator::simulate($bgRgb),
            ],
        ];

        foreach ($simulations as $type => $sim) {
            $contrast = ContrastRatioCalculator::calculate($sim['fg'], $sim['bg']);
            if ($contrast < 4.5) {
                return $this->createViolation(
                    $element,
                    sprintf('Insufficient color contrast (%.2f:1) under %s color blindness simulation (minimum 4.5:1 required).', $contrast, $type),
                    sprintf('Adjust foreground color (%s) or background color (%s) to increase contrast for users with %s.', $colorHex, $bgHex, $type)
                );
            }
        }

        return null;
    }

    private function extractColorFromStyle(string $style, string $property): ?string
    {
        if (preg_match('/' . preg_quote($property, '/') . '\s*:\s*(#[0-9a-fA-F]{3,6})/i', $style, $matches)) {
            $hex = $matches[1];
            if (strlen($hex) === 4) {
                $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
            }
            return $hex;
        }
        return null;
    }
}
