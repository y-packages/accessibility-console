<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class Wcag22AAPreset implements PresetInterface
{
    public function getName(): string
    {
        return 'wcag22-aa';
    }

    public function getDescription(): string
    {
        return 'Full W3C WCAG 2.2 Level A & AA compliance profile.';
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_2_4_11_FOCUS_NOT_OBSCURED',
            'WCAG_2_5_7_DRAGGING_MOVEMENTS',
            'WCAG_2_5_8_TARGET_SIZE',
            'WCAG_2_5_8_TARGET_SPACING',
            'WCAG_3_2_6_CONSISTENT_HELP',
            'WCAG_3_3_7_REDUNDANT_ENTRY',
            'WCAG_3_3_8_ACCESSIBLE_AUTH',
            'WCAG_1_4_3_CONTRAST',
            'WCAG_2_4_7_FOCUS_OUTLINE',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
