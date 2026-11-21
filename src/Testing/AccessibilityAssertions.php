<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Testing;

use PHPUnit\Framework\Assert;
use YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

/**
 * Drop-in PHPUnit trait providing expressive accessibility assertions for
 * Laravel, Symfony, and standard PHP test suites.
 */
trait AccessibilityAssertions
{
    /**
     * Assert that the given HTML string has zero ERROR/CRITICAL accessibility violations
     * (and optionally zero or bounded WARNING violations).
     */
    protected function assertHtmlAccessible(
        string $html,
        int $level = 4,
        ?int $maxWarnings = null,
        string $message = ''
    ): void {
        $violations = $this->runAccessibilityScan($html, $level);
        $errors = [];
        $warnings = [];

        foreach ($violations as $v) {
            if ($v->severity === Severity::CRITICAL || $v->severity === Severity::ERROR) {
                $errors[] = $v;
            } elseif ($v->severity === Severity::WARNING) {
                $warnings[] = $v;
            }
        }

        Assert::assertEmpty(
            $errors,
            ($message !== '' ? $message . "\n" : '') . sprintf(
                "Expected HTML to have 0 critical/error accessibility violations (Level %d), found %d:\n%s",
                $level,
                count($errors),
                $this->formatViolationList($errors)
            )
        );

        if ($maxWarnings !== null) {
            Assert::assertLessThanOrEqual(
                $maxWarnings,
                count($warnings),
                ($message !== '' ? $message . "\n" : '') . sprintf(
                    "Expected at most %d accessibility warning(s), found %d:\n%s",
                    $maxWarnings,
                    count($warnings),
                    $this->formatViolationList($warnings)
                )
            );
        }
    }

    /**
     * Assert that the HTML meets or exceeds a minimum accessibility health score (0-100).
     */
    protected function assertAccessibilityScoreAtLeast(
        string $html,
        int $minScore,
        int $level = 4,
        string $message = ''
    ): void {
        $violations = $this->runAccessibilityScan($html, $level);
        $metrics = AccessibilityScoreCalculator::calculate($violations);
        $actual = $metrics['score'];

        Assert::assertGreaterThanOrEqual(
            $minScore,
            $actual,
            ($message !== '' ? $message . ' ' : '') . sprintf(
                "Accessibility score %d/100 is below expected minimum %d/100 (%d violations).\n%s",
                $actual,
                $minScore,
                count($violations),
                $this->formatViolationList($violations)
            )
        );
    }

    /**
     * Assert that HTML has zero critical/error violations under a named compliance preset.
     */
    protected function assertMeetsAccessibilityPreset(
        string $html,
        string $presetName = 'wcag22-aa',
        string $message = ''
    ): void {
        $registry = new PresetRegistry();
        $preset = $registry->get($presetName);
        Assert::assertNotNull($preset, "Unknown accessibility preset '{$presetName}'.");

        $scanner = new Scanner();
        $excluded = $preset->getExcludedRules();
        $active = [];

        foreach (RuleLevels::getRulesForLevel($preset->getLevel()) as $rule) {
            $id = $rule->getId();
            if (!in_array($id, $excluded, true)) {
                $active[$id] = $rule;
            }
        }
        foreach (RuleLevels::getRulesForLevel(9) as $rule) {
            $id = $rule->getId();
            if (in_array($id, $preset->getIncludedRules(), true) && !in_array($id, $excluded, true)) {
                $active[$id] = $rule;
            }
        }
        foreach ($active as $rule) {
            $scanner->addRule($rule);
        }

        $violations = $scanner->scan($html);
        $severe = array_values(array_filter(
            $violations,
            static fn(Violation $v): bool => $v->severity === Severity::CRITICAL || $v->severity === Severity::ERROR
        ));

        Assert::assertEmpty(
            $severe,
            ($message !== '' ? $message . "\n" : '') . sprintf(
                "HTML failed compliance preset '%s' with %d severe violation(s):\n%s",
                $presetName,
                count($severe),
                $this->formatViolationList($severe)
            )
        );
    }

    /**
     * Assert that a specific accessibility rule ID is not violated in the HTML.
     */
    protected function assertNoAccessibilityRuleViolation(
        string $html,
        string $ruleId,
        string $message = ''
    ): void {
        $violations = $this->runAccessibilityScan($html, 5);
        $matched = array_values(array_filter(
            $violations,
            static fn(Violation $v): bool => $v->ruleId === $ruleId
        ));

        Assert::assertEmpty(
            $matched,
            ($message !== '' ? $message . "\n" : '') . sprintf(
                "Expected no violation of rule '%s', found %d:\n%s",
                $ruleId,
                count($matched),
                $this->formatViolationList($matched)
            )
        );
    }

    /**
     * @return Violation[]
     */
    private function runAccessibilityScan(string $html, int $level): array
    {
        $scanner = new Scanner();
        foreach (RuleLevels::getRulesForLevel($level) as $rule) {
            $scanner->addRule($rule);
        }
        return $scanner->scan($html);
    }

    /**
     * @param Violation[] $violations
     */
    private function formatViolationList(array $violations): string
    {
        $lines = [];
        foreach (array_slice($violations, 0, 10) as $v) {
            $lines[] = sprintf('  - [%s] (%s) %s', $v->ruleId, strtoupper($v->severity->value), $v->message);
        }
        return implode("\n", $lines);
    }
}
