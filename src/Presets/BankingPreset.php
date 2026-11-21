<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class BankingPreset implements PresetInterface
{
    public function getName(): string
    {
        return 'banking';
    }

    public function getDescription(): string
    {
        return 'Financial services & digital banking accessibility profile (EAA 2025 + WCAG 2.2 AA).';
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_2_2_1_TIMEOUT_WARNING',
            'WCAG_3_3_1_FORM_ERROR_SUMMARY',
            'WCAG_3_3_7_REDUNDANT_ENTRY',
            'WCAG_3_3_8_ACCESSIBLE_AUTH',
            'WCAG_4_1_2_PASSWORD_VISIBILITY_TOGGLE',
            'WCAG_4_1_3_STATUS_MESSAGES',
            'WCAG_1_4_3_CONTRAST',
            'WCAG_2_5_8_TARGET_SIZE',
            'WCAG_2_4_7_FOCUS_OUTLINE',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
