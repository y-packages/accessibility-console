<?php

namespace YakNet\AccessibilityConsole\Presets;

class En301549Preset implements PresetInterface
{
    public function getName(): string
    {
        return 'en301549';
    }

    public function getDescription(): string
    {
        return 'European Union EN 301 549 public sector digital accessibility profile.';
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
            'WCAG_1_4_12_TEXT_SPACING',
            'WCAG_2_5_3_LABEL_IN_NAME',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
