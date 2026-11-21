<?php

use YakNet\AccessibilityConsole\Analytics\ComplianceMatrixGenerator;
use YakNet\AccessibilityConsole\Compliance\VpatReportGenerator;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;
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

if (!function_exists('a11y_scan_preset')) {
    /**
     * Scan HTML content using a named regulatory or sectoral compliance preset.
     *
     * @param string $html
     * @param string $presetName e.g. 'wcag22-aa', 'en301549', 'section508', 'government', 'healthcare', 'banking'
     * @return array<int, \YakNet\AccessibilityConsole\Core\Violation>
     */
    function a11y_scan_preset(string $html, string $presetName = 'wcag22-aa'): array
    {
        $registry = new PresetRegistry();
        $preset = $registry->get($presetName);
        $level = $preset !== null ? $preset->getLevel() : 4;
        $excluded = $preset !== null ? $preset->getExcludedRules() : [];
        $included = $preset !== null ? $preset->getIncludedRules() : [];

        $scanner = new Scanner();
        $active = [];
        foreach (RuleLevels::getRulesForLevel($level) as $rule) {
            $id = $rule->getId();
            if (!in_array($id, $excluded, true)) {
                $active[$id] = $rule;
            }
        }
        if (!empty($included)) {
            foreach (RuleLevels::getRulesForLevel(9) as $rule) {
                $id = $rule->getId();
                if (in_array($id, $included, true) && !in_array($id, $excluded, true)) {
                    $active[$id] = $rule;
                }
            }
        }
        foreach ($active as $rule) {
            $scanner->addRule($rule);
        }

        return $scanner->scan($html);
    }
}

if (!function_exists('a11y_vpat')) {
    /**
     * Generate a VPAT 2.5 Accessibility Conformance Report from HTML content.
     *
     * @param string $html
     * @param string $format 'markdown', 'html', or 'json'
     * @param string $productName
     * @return string
     */
    function a11y_vpat(string $html, string $format = 'markdown', string $productName = 'Web Application'): string
    {
        $violations = a11y_scan($html, 5);
        $generator = new VpatReportGenerator();
        return match (strtolower($format)) {
            'html' => $generator->renderHtml($violations, $productName),
            'json' => $generator->renderJson($violations, $productName),
            default => $generator->renderMarkdown($violations, $productName),
        };
    }
}

if (!function_exists('a11y_compliance_matrix')) {
    /**
     * Generate a WCAG 2.1 / 2.2 POUR compliance matrix from HTML content.
     *
     * @param string $html
     * @param int $level
     * @return array{
     *     overall_compliance_percentage: float,
     *     principles: array<string, array{violations: int, status: string}>,
     *     levels: array<string, array{violations: int, percentage: float}>
     * }
     */
    function a11y_compliance_matrix(string $html, int $level = 4): array
    {
        $violations = a11y_scan($html, $level);
        return (new ComplianceMatrixGenerator())->generate($violations, count(RuleLevels::getRulesForLevel($level)));
    }
}
