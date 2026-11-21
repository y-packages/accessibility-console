<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Policy;

use YakNet\AccessibilityConsole\Analytics\ComplianceMatrixGenerator;
use YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;

/**
 * Governance Policy Engine for enforcing organization-wide accessibility thresholds,
 * severity overrides, zero-tolerance rules, and WCAG standard gates in CI/CD pipelines.
 */
class PolicyEngine
{
    /** @var array<string, mixed> */
    private array $policy;

    /**
     * @param array<string, mixed> $policy
     */
    public function __construct(array $policy = [])
    {
        $this->policy = $policy;
    }

    /**
     * Apply configured severity overrides to violations in-place.
     *
     * @param Violation[] $violations
     * @return Violation[]
     */
    public function applySeverityOverrides(array $violations): array
    {
        $overrides = $this->policy['severity_overrides'] ?? [];
        if (!is_array($overrides) || empty($overrides)) {
            return $violations;
        }

        foreach ($violations as $violation) {
            if (isset($overrides[$violation->ruleId]) && is_string($overrides[$violation->ruleId])) {
                $mapped = match (strtolower($overrides[$violation->ruleId])) {
                    'critical' => Severity::CRITICAL,
                    'error' => Severity::ERROR,
                    'warning' => Severity::WARNING,
                    'info' => Severity::INFO,
                    default => null,
                };
                if ($mapped !== null) {
                    $violation->severity = $mapped;
                }
            }
        }

        return $violations;
    }

    /**
     * Evaluate violations against all configured governance policy gates.
     *
     * @param Violation[] $violations
     */
    public function evaluate(array $violations): PolicyResult
    {
        $this->applySeverityOverrides($violations);

        $metrics = AccessibilityScoreCalculator::calculate($violations);
        $score = $metrics['score'];

        $counts = [
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'info' => 0,
        ];
        $standardCounts = ['A' => 0, 'AA' => 0, 'AAA' => 0];
        $ruleCounts = [];

        foreach ($violations as $v) {
            $counts[$v->severity->value]++;
            $stdKey = $v->standard->value;
            $standardCounts[$stdKey]++;
            $ruleCounts[$v->ruleId] = ($ruleCounts[$v->ruleId] ?? 0) + 1;
        }

        /** @var string[] $failures */
        $failures = [];

        // 1. Minimum score threshold
        if (isset($this->policy['min_score']) && is_numeric($this->policy['min_score'])) {
            $minScore = (int)$this->policy['min_score'];
            if ($score < $minScore) {
                $failures[] = sprintf('Health score (%d/100) is below policy minimum (%d/100).', $score, $minScore);
            }
        }

        // 2. Severity maximums
        $severityLimits = [
            'max_critical' => 'critical',
            'max_errors' => 'error',
            'max_warnings' => 'warning',
        ];
        foreach ($severityLimits as $policyKey => $sevKey) {
            if (isset($this->policy[$policyKey]) && is_numeric($this->policy[$policyKey])) {
                $limit = (int)$this->policy[$policyKey];
                $actual = $counts[$sevKey];
                if ($actual > $limit) {
                    $failures[] = sprintf('Policy limit exceeded for %s violations: found %d, maximum allowed is %d.', $sevKey, $actual, $limit);
                }
            }
        }

        // 3. Required WCAG Standards (zero-tolerance standards)
        if (isset($this->policy['required_standards']) && is_array($this->policy['required_standards'])) {
            foreach ($this->policy['required_standards'] as $std) {
                if (!is_string($std)) {
                    continue;
                }
                $upper = strtoupper(trim($std));
                $stdViolations = $standardCounts[$upper] ?? 0;
                if ($stdViolations > 0) {
                    $failures[] = sprintf('WCAG Level %s compliance required by policy, but %d violation(s) were detected.', $upper, $stdViolations);
                }
            }
        }

        // 4. Banned / Zero-Tolerance Rules
        if (isset($this->policy['banned_rules']) && is_array($this->policy['banned_rules'])) {
            foreach ($this->policy['banned_rules'] as $bannedRule) {
                if (!is_string($bannedRule)) {
                    continue;
                }
                if (isset($ruleCounts[$bannedRule])) {
                    $failures[] = sprintf('Zero-tolerance rule "%s" was violated %d time(s).', $bannedRule, $ruleCounts[$bannedRule]);
                }
            }
        }

        // 5. POUR Principle Thresholds
        if (isset($this->policy['principle_limits']) && is_array($this->policy['principle_limits'])) {
            $matrixGen = new ComplianceMatrixGenerator();
            $matrix = $matrixGen->generate($violations);
            foreach ($this->policy['principle_limits'] as $principle => $maxAllowed) {
                if (!is_string($principle) || !is_numeric($maxAllowed)) {
                    continue;
                }
                $actual = $matrix['principles'][$principle]['violations'] ?? 0;
                if ($actual > (int)$maxAllowed) {
                    $failures[] = sprintf('POUR Principle "%s" has %d violation(s), exceeding policy limit of %d.', $principle, $actual, (int)$maxAllowed);
                }
            }
        }

        return new PolicyResult(
            passed: empty($failures),
            failures: $failures,
            summary: [
                'score' => $score,
                'counts' => $counts,
                'standards' => $standardCounts,
            ]
        );
    }

    public function hasPolicies(): bool
    {
        return !empty($this->policy);
    }
}
