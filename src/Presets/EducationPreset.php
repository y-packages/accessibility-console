<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Presets;

class EducationPreset implements PresetInterface
{
    public function getName(): string
    {
        return 'education';
    }

    public function getDescription(): string
    {
        return 'Higher education, LMS & academic portal accessibility profile (ADA Title II & WCAG 2.2 AA).';
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function getIncludedRules(): array
    {
        return [
            'WCAG_1_1_1_ALT',
            'WCAG_1_2_1_AudioTrack',
            'WCAG_1_2_2_VIDEO_TRACK',
            'WCAG_1_2_2_TRACK_VALIDATION',
            'WCAG_1_3_1_TABLE',
            'WCAG_1_3_1_TABLE_CAPTION',
            'WCAG_2_4_4_LINK_DOWNLOAD_CONTEXT',
            'WCAG_2_4_6_DOCUMENT_H1',
            'WCAG_3_1_1_LANG',
            'WCAG_3_1_4_ABBR_TITLE',
        ];
    }

    public function getExcludedRules(): array
    {
        return [];
    }
}
