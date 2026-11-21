<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class Bitv2Preset implements PresetInterface
{
    public function getName(): string
    {
        return 'bitv2';
    }

    public function getDescription(): string
    {
        return 'German BITV 2.0 (Barrierefreie-Informationstechnik-Verordnung) public accessibility profile.';
    }

    public function getLevel(): int
    {
        return 5;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_4_3_CONTRAST',
            'WCAG_1_4_12_TEXT_SPACING',
            'WCAG_2_4_7_FOCUS_OUTLINE',
            'WCAG_3_1_1_LANG',
            'WCAG_3_1_2_LANG',
            'WCAG_3_1_4_ABBR_TITLE',
            'WCAG_2_4_1_SkipNavigation',
            'WCAG_2_4_6_DOCUMENT_H1',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
