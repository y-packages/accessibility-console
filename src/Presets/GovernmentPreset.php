<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class GovernmentPreset implements PresetInterface
{
    public function getName(): string
    {
        return 'government';
    }

    public function getDescription(): string
    {
        return 'Public sector & e-Government digital accessibility profile (WCAG 2.2 AA + EN 301 549).';
    }

    public function getLevel(): int
    {
        return 5;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_1_1_ALT',
            'WCAG_1_4_3_CONTRAST',
            'WCAG_2_4_1_SkipNavigation',
            'WCAG_2_4_2_PAGE_TITLE',
            'WCAG_2_4_6_DOCUMENT_H1',
            'WCAG_2_4_7_FOCUS_OUTLINE',
            'WCAG_3_1_1_LANG',
            'WCAG_3_3_1_FORM_ERROR_SUMMARY',
            'WCAG_3_3_8_ACCESSIBLE_AUTH',
            'WCAG_2_4_4_LINK_DOWNLOAD_CONTEXT',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
