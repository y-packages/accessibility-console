<?php

namespace YakNet\AccessibilityConsole\Core\Config;

class SmartPresetBuilder
{
    /**
     * Analyze HTML content features to suggest rules presets.
     *
     * @param string $html
     * @return array{suggestedLevel: int, tagsCheckedCount: array<string, int>, recommendedExclusions: string[]}
     */
    public function analyze(string $html): array
    {
        $tagsChecked = ['img' => 0, 'form' => 0, 'table' => 0, 'svg' => 0, 'video' => 0];

        foreach (array_keys($tagsChecked) as $tag) {
            $tagsChecked[$tag] = substr_count(strtolower($html), '<' . $tag);
        }

        // Calculate recommendations
        $suggestedLevel = 3; // Standard baseline
        if ($tagsChecked['form'] > 5 || $tagsChecked['table'] > 3) {
            $suggestedLevel = 4; // Higher complexity requires more strict tests
        }
        if ($tagsChecked['video'] > 0) {
            $suggestedLevel = 5; // Video elements need caption checks, standard level 5
        }

        $exclusions = [];
        if ($tagsChecked['svg'] === 0) {
            $exclusions[] = 'WCAG_1_1_1_SVG';
        }
        if ($tagsChecked['video'] === 0) {
            $exclusions[] = 'WCAG_1_2_2_VIDEO_TRACK';
        }

        return [
            'suggestedLevel' => $suggestedLevel,
            'tagsCheckedCount' => $tagsChecked,
            'recommendedExclusions' => $exclusions,
        ];
    }
}
