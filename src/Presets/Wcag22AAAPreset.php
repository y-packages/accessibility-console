<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class Wcag22AAAPreset implements PresetInterface
{
    public function getName(): string
    {
        return 'wcag22-aaa';
    }

    public function getDescription(): string
    {
        return 'Strictest W3C WCAG 2.2 Level A, AA & AAA compliance profile (all 175 rules).';
    }

    public function getLevel(): int
    {
        return 5;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_4_6_CONTRAST_AAA',
            'WCAG_2_4_13_FOCUS_APPEARANCE',
            'WCAG_2_3_3_REDUCED_MOTION',
            'WCAG_3_1_4_ABBR_TITLE',
            'WCAG_1_4_8_TEXT_JUSTIFY',
            'WCAG_3_1_5_ALL_CAPS_LONG',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
