<?php

use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\RuleLevels;
use YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer;
use YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator;

if (!function_exists('a11y_scan')) {
    /**
     * Scan HTML content for accessibility violations.
     *
     * @param string $html
     * @param int $level
     * @return array<int, \YakNet\AccessibilityConsole\Core\Violation>
     */
    function a11y_scan(string $html, int $level = 4): array
    {
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($level);
        foreach ($rules as $rule) {
            $scanner->addRule($rule);
        }
        return $scanner->scan($html);
    }
}

if (!function_exists('a11y_score')) {
    /**
     * Calculate accessibility score (0-100) for HTML content.
     *
     * @param string $html
     * @param int $level
     * @return int
     */
    function a11y_score(string $html, int $level = 4): int
    {
        $violations = a11y_scan($html, $level);
        $metrics = AccessibilityScoreCalculator::calculate($violations);
        return $metrics['score'];
    }
}

if (!function_exists('a11y_quick_fix')) {
    /**
     * Perform local self-healing fixes on the HTML content.
     *
     * @param string $html
     * @param int $level
     * @return string
     */
    function a11y_quick_fix(string $html, int $level = 4): string
    {
        $violations = a11y_scan($html, $level);
        $healer = new LocalHealer();

        foreach ($violations as $v) {
            $healed = $healer->heal($v);
            if ($healed !== null && str_contains($healed, 'FIX:')) {
                preg_match('/FIX:(.*)/s', $healed, $matches);
                $suggestion = trim($matches[1] ?? '');
                $replacedSuggestion = preg_replace('/^```html\s*|\s*```$/i', '', $suggestion);
                $suggestion = is_string($replacedSuggestion) ? $replacedSuggestion : '';
                
                if ($suggestion !== '') {
                    $htmlSnippet = $v->htmlSnippet;
                    if (str_contains($html, $htmlSnippet)) {
                        $html = str_replace($htmlSnippet, $suggestion, $html);
                    } else {
                        $html = str_replace(trim($htmlSnippet), trim($suggestion), $html);
                    }
                }
            }
        }

        return $html;
    }
}
