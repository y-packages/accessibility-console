<?php

namespace YakNet\AccessibilityConsole\Rules;

class RuleExecutionStats
{
    /**
     * @var array<string, array{invocations: int, total_time_ms: float, violations: int}>
     */
    private array $stats = [];

    /**
     * Record a rule execution metric.
     */
    public function record(string $ruleId, float $durationMs, bool $hasViolation = false): void
    {
        if (!isset($this->stats[$ruleId])) {
            $this->stats[$ruleId] = [
                'invocations' => 0,
                'total_time_ms' => 0.0,
                'violations' => 0,
            ];
        }

        $this->stats[$ruleId]['invocations']++;
        $this->stats[$ruleId]['total_time_ms'] += $durationMs;
        if ($hasViolation) {
            $this->stats[$ruleId]['violations']++;
        }
    }

    /**
     * Get top slowest rules by total execution time.
     *
     * @return array<string, array{invocations: int, total_time_ms: float, avg_time_ms: float, violations: int}>
     */
    public function getSlowestRules(int $limit = 10): array
    {
        $calculated = [];
        foreach ($this->stats as $id => $data) {
            $avg = $data['invocations'] > 0 ? ($data['total_time_ms'] / $data['invocations']) : 0.0;
            $calculated[$id] = [
                'invocations' => $data['invocations'],
                'total_time_ms' => round($data['total_time_ms'], 3),
                'avg_time_ms' => round($avg, 4),
                'violations' => $data['violations'],
            ];
        }

        uasort($calculated, function ($a, $b) {
            return $b['total_time_ms'] <=> $a['total_time_ms'];
        });

        return array_slice($calculated, 0, $limit, true);
    }

    /**
     * Get all recorded statistics.
     *
     * @return array<string, array{invocations: int, total_time_ms: float, violations: int}>
     */
    public function getAllStats(): array
    {
        return $this->stats;
    }

    /**
     * Reset all stats counters.
     */
    public function reset(): void
    {
        $this->stats = [];
    }
}
