<?php

namespace YakNet\AccessibilityConsole\AI\Providers;

use YakNet\AccessibilityConsole\AI\AiProviderInterface;
use YakNet\AccessibilityConsole\Core\Violation;

class MockAiProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'Mock AI';
    }

    public function suggestFix(Violation $violation): ?string
    {
        $snippet = $violation->htmlSnippet;
        $fixedSnippet = $snippet;
        
        // Simple mock fixes based on rule ID
        if ($violation->ruleId === 'WCAG_2_2_2_BLINK') {
            $fixedSnippet = str_replace(['<blink>', '</blink>'], ['<span>', '</span>'], $snippet);
        } elseif ($violation->ruleId === 'WCAG_1_3_1_DUPLICATE_H1') {
            $fixedSnippet = str_replace(['<h1>', '</h1>'], ['<h2>', '</h2>'], $snippet);
        } elseif ($violation->ruleId === 'WCAG_1_3_5_AUTOCOMPLETE') {
            $fixedSnippet = str_replace('<input', '<input autocomplete="email"', $snippet);
        } elseif ($violation->ruleId === 'WCAG_2_1_1_SCROLLABLE_FOCUS') {
            $fixedSnippet = str_replace('<div', '<div tabindex="0"', $snippet);
        } elseif ($violation->ruleId === 'WCAG_4_1_2_ARIA_LABELLEDBY') {
            $fixedSnippet = str_replace('aria-labelledby="non_existent_label"', 'aria-labelledby="real_label"', $snippet);
        } elseif ($violation->ruleId === 'WCAG_1_1_1_ALT_PLACEHOLDER') {
            if (str_contains($snippet, 'alt="logo"')) {
                $fixedSnippet = str_replace('alt="logo"', 'alt="YakNet Accessibility Console Company Logo"', $snippet);
            } else {
                $fixedSnippet = str_replace('alt="header_banner.jpg"', 'alt="YakNet Accessibility Console Marketing Banner"', $snippet);
            }
        } elseif ($violation->ruleId === 'WCAG_1_1_1_SVG') {
            $fixedSnippet = str_replace('<svg', '<svg aria-hidden="true"', $snippet);
        } elseif ($violation->ruleId === 'WCAG_1_3_1_TABLE_SUMMARY') {
            $fixedSnippet = preg_replace('/summary=".*?"/i', '', $snippet) ?: $snippet;
        } elseif ($violation->ruleId === 'WCAG_1_3_1_PRESENTATION_TAGS') {
            $fixedSnippet = str_replace(['<center>', '</center>', '<strike>', '</strike>'], ['<span style="text-align: center; display: block;">', '</span>', '<span style="text-decoration: line-through;">', '</span>'], $snippet);
        } elseif ($violation->ruleId === 'WCAG_2_4_3_AUTOFOCUS') {
            $fixedSnippet = str_replace(' autofocus', '', $snippet);
        }
        
        return "EXPLANATION: Mocked correction for accessibility validation.\nFIX: " . $fixedSnippet;
    }
}
