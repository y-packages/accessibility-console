<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class HealthcarePreset implements PresetInterface
{
    public function getName(): string
    {
        return 'healthcare';
    }

    public function getDescription(): string
    {
        return 'Healthcare & patient portal accessibility profile (HHS Section 504 & WCAG 2.2 AA/AAA readability).';
    }

    public function getLevel(): int
    {
        return 5;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_4_3_CONTRAST',
            'WCAG_1_4_4_FONT_SIZE_MIN',
            'WCAG_1_4_12_TEXT_SPACING',
            'WCAG_2_2_1_TIMEOUT_WARNING',
            'WCAG_2_3_1_FLASHING_CONTENT',
            'WCAG_2_3_3_REDUCED_MOTION',
            'WCAG_3_2_6_CONSISTENT_HELP',
            'WCAG_3_3_1_FORM_ERROR_SUMMARY',
            'WCAG_3_3_7_REDUNDANT_ENTRY',
            'WCAG_3_3_8_ACCESSIBLE_AUTH',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
