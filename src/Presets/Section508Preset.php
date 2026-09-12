<?php

namespace YakNet\AccessibilityConsole\Presets;

class Section508Preset implements PresetInterface
{
    public function getName(): string
    {
        return 'section508';
    }

    public function getDescription(): string
    {
        return 'US Federal Government Section 508 accessibility compliance profile.';
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_1_1_IMG_ALT',
            'WCAG_1_3_1_TABLE',
            'WCAG_1_3_1_TABLE_HEADERS_ATTR',
            'WCAG_1_3_1_FORM_LABEL',
            'WCAG_2_1_1_KEYBOARD_CLICK',
            'WCAG_2_4_2_PAGE_TITLE',
            'WCAG_3_1_1_LANG',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
