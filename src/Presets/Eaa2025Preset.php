<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class Eaa2025Preset implements PresetInterface
{
    public function getName(): string
    {
        return 'eaa2025';
    }

    public function getDescription(): string
    {
        return 'European Accessibility Act (Directive EU 2019/882) digital services & products profile.';
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_4_3_CONTRAST',
            'WCAG_2_4_7_FOCUS_OUTLINE',
            'WCAG_2_5_8_TARGET_SIZE',
            'WCAG_3_3_8_ACCESSIBLE_AUTH',
            'WCAG_3_3_7_REDUNDANT_ENTRY',
            'WCAG_1_4_12_TEXT_SPACING',
            'WCAG_2_5_3_LABEL_IN_NAME',
            'WCAG_1_3_4_ORIENTATION_LOCK',
            'WCAG_4_1_3_STATUS_MESSAGES',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
