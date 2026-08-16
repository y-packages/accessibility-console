<?php

namespace YakNet\AccessibilityConsole\Analytics;

use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;

class AccessibilityTelemetry
{
    /**
     * Compute violation density, error distribution, and priority impact metrics.
     *
     * @param Violation[] $violations
     * @param int $totalLinesOfCode
     * @return array{
     *     total_violations: int,
     *     error_count: int,
     *     warning_count: int,
     *     defect_density_per_kloc: float,
     *     critical_blockers_ratio: float
     * }
     */
    public function computeMetrics(array $violations, int $totalLinesOfCode = 1000): array
    {
        $errorCount = 0;
        $warningCount = 0;

        foreach ($violations as $v) {
            if ($v->severity === Severity::ERROR) {
                $errorCount++;
            } else {
                $warningCount++;
            }
        }

        $totalViolations = count($violations);
        $kloc = max(1.0, $totalLinesOfCode / 1000.0);
        $defectDensity = round($totalViolations / $kloc, 2);
        $criticalRatio = ($totalViolations > 0) ? round(($errorCount / $totalViolations) * 100, 1) : 0.0;

        return [
            'total_violations' => $totalViolations,
            'error_count' => $errorCount,
            'warning_count' => $warningCount,
            'defect_density_per_kloc' => $defectDensity,
            'critical_blockers_ratio' => $criticalRatio,
        ];
    }
}
