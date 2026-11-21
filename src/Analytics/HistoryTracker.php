<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Analytics;

use YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;

/**
 * Persists accessibility scan snapshots over time, computes multi-run health trends,
 * and performs regression/improvement comparisons between two scan reports.
 */
class HistoryTracker
{
    /**
     * Record a scan snapshot into a JSON history file.
     *
     * @param Violation[] $violations
     * @param string $historyFile
     * @param string $target
     * @param string|null $preset
     * @return array<string, mixed> The newly recorded snapshot
     */
    public function record(
        array $violations,
        string $historyFile,
        string $target = '.',
        ?string $preset = null
    ): array {
        $entries = $this->load($historyFile);
        $metrics = AccessibilityScoreCalculator::calculate($violations);
        $matrix = (new ComplianceMatrixGenerator())->generate($violations);

        $counts = ['critical' => 0, 'error' => 0, 'warning' => 0, 'info' => 0];
        $ruleBreakdown = [];

        foreach ($violations as $v) {
            $counts[$v->severity->value]++;
            $ruleBreakdown[$v->ruleId] = ($ruleBreakdown[$v->ruleId] ?? 0) + 1;
        }

        $snapshot = [
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'target' => $target,
            'preset' => $preset,
            'score' => $metrics['score'],
            'total_violations' => count($violations),
            'severity' => $counts,
            'principles' => $matrix['principles'],
            'rules' => $ruleBreakdown,
        ];

        $entries[] = $snapshot;
        $dir = dirname($historyFile);
        if (!is_dir($dir) && $dir !== '.' && $dir !== '') {
            @mkdir($dir, 0775, true);
        }
        file_put_contents($historyFile, (string)json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $snapshot;
    }

    /**
     * Load all recorded snapshots from a JSON history file.
     *
     * @return array<int, array<string, mixed>>
     */
    public function load(string $historyFile): array
    {
        if (!file_exists($historyFile)) {
            return [];
        }
        $raw = @file_get_contents($historyFile);
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Compute overall accessibility health trend across historical runs.
     *
     * @param array<int, array<string, mixed>> $entries
     * @return array{runs: int, latest_score: int, previous_score: int|null, score_delta: int, trend: string}
     */
    public function computeTrend(array $entries): array
    {
        $runs = count($entries);
        if ($runs === 0) {
            return [
                'runs' => 0,
                'latest_score' => 100,
                'previous_score' => null,
                'score_delta' => 0,
                'trend' => 'stable',
            ];
        }

        $latest = $entries[$runs - 1];
        $latestScore = is_numeric($latest['score'] ?? null) ? (int)$latest['score'] : 0;

        if ($runs === 1) {
            return [
                'runs' => 1,
                'latest_score' => $latestScore,
                'previous_score' => null,
                'score_delta' => 0,
                'trend' => 'baseline',
            ];
        }

        $prev = $entries[$runs - 2];
        $prevScore = is_numeric($prev['score'] ?? null) ? (int)$prev['score'] : 0;
        $delta = $latestScore - $prevScore;

        $trend = $delta > 0 ? 'improving' : ($delta < 0 ? 'regressing' : 'stable');

        return [
            'runs' => $runs,
            'latest_score' => $latestScore,
            'previous_score' => $prevScore,
            'score_delta' => $delta,
            'trend' => $trend,
        ];
    }

    /**
     * Compare two serialized violation arrays (from JSON scan reports) and compute
     * regressions (newly introduced issues), resolved issues, and score delta.
     *
     * @param array<int, array<string, mixed>> $beforeViolations
     * @param array<int, array<string, mixed>> $afterViolations
     * @return array{
     *     before_count: int,
     *     after_count: int,
     *     before_score: int,
     *     after_score: int,
     *     score_delta: int,
     *     new_violations: array<int, array<string, mixed>>,
     *     resolved_violations: array<int, array<string, mixed>>,
     *     unchanged_count: int
     * }
     */
    public function compareReports(array $beforeViolations, array $afterViolations): array
    {
        $beforeMap = [];
        foreach ($beforeViolations as $idx => $v) {
            $fp = $this->fingerprint($v) . '#' . $idx;
            $baseFp = $this->fingerprint($v);
            $beforeMap[$baseFp][] = $v;
        }

        $afterMap = [];
        foreach ($afterViolations as $v) {
            $baseFp = $this->fingerprint($v);
            $afterMap[$baseFp][] = $v;
        }

        $newViolations = [];
        $resolvedViolations = [];
        $unchangedCount = 0;

        $allKeys = array_unique(array_merge(array_keys($beforeMap), array_keys($afterMap)));

        foreach ($allKeys as $key) {
            $bList = $beforeMap[$key] ?? [];
            $aList = $afterMap[$key] ?? [];
            $bCount = count($bList);
            $aCount = count($aList);

            $common = min($bCount, $aCount);
            $unchangedCount += $common;

            if ($aCount > $bCount) {
                for ($i = $bCount; $i < $aCount; $i++) {
                    $newViolations[] = $aList[$i];
                }
            } elseif ($bCount > $aCount) {
                for ($i = $aCount; $i < $bCount; $i++) {
                    $resolvedViolations[] = $bList[$i];
                }
            }
        }

        $beforeScore = $this->estimateScoreFromRaw($beforeViolations);
        $afterScore = $this->estimateScoreFromRaw($afterViolations);

        return [
            'before_count' => count($beforeViolations),
            'after_count' => count($afterViolations),
            'before_score' => $beforeScore,
            'after_score' => $afterScore,
            'score_delta' => $afterScore - $beforeScore,
            'new_violations' => $newViolations,
            'resolved_violations' => $resolvedViolations,
            'unchanged_count' => $unchangedCount,
        ];
    }

    /**
     * @param array<string, mixed> $violation
     */
    private function fingerprint(array $violation): string
    {
        $ruleId = is_scalar($violation['ruleId'] ?? null) ? (string)$violation['ruleId'] : '';
        $snippet = is_scalar($violation['htmlSnippet'] ?? null) ? trim((string)$violation['htmlSnippet']) : '';
        $file = '';
        if (isset($violation['location']) && is_array($violation['location']) && is_scalar($violation['location']['file'] ?? null)) {
            $file = (string)$violation['location']['file'];
        }
        return hash('sha256', $file . '|' . $ruleId . '|' . preg_replace('/\s+/', ' ', $snippet));
    }

    /**
     * @param array<int, array<string, mixed>> $rawViolations
     */
    private function estimateScoreFromRaw(array $rawViolations): int
    {
        $score = 100;
        foreach ($rawViolations as $v) {
            $sev = strtolower(is_scalar($v['severity'] ?? null) ? (string)$v['severity'] : 'warning');
            $deduction = match ($sev) {
                Severity::CRITICAL->value => 15,
                Severity::ERROR->value => 8,
                default => 3,
            };
            $score -= $deduction;
        }
        return max(0, $score);
    }
}
