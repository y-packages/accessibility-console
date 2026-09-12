<?php

namespace YakNet\AccessibilityConsole\Presets;

class ECommercePreset implements PresetInterface
{
    public function getName(): string
    {
        return 'ecommerce';
    }

    public function getDescription(): string
    {
        return 'E-Commerce storefront and checkout flow accessibility profile.';
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_1_1_IMG_ALT',
            'WCAG_4_1_2_BUTTON',
            'WCAG_1_3_1_FORM_LABEL',
            'WCAG_3_3_7_REDUNDANT_ENTRY',
            'WCAG_2_5_8_TARGET_SIZE',
            'WCAG_1_4_3_CONTRAST',
            'WCAG_4_1_2_DIALOG',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
