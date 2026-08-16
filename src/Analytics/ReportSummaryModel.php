<?php

namespace YakNet\AccessibilityConsole\Analytics;

class ReportSummaryModel
{
    /**
     * @param array<string, int> $violationCountsBySeverity
     * @param array<string, int> $violationCountsByStandard
     * @param array<string, int> $violationCountsByPrinciple
     * @param array<string, mixed> $complianceScores
     */
    public function __construct(
        public readonly int $totalViolations,
        public readonly int $totalFilesScanned,
        public readonly int $baselinedCount,
        public readonly int $healthScore,
        public readonly array $violationCountsBySeverity,
        public readonly array $violationCountsByStandard,
        public readonly array $violationCountsByPrinciple,
        public readonly array $complianceScores,
        public readonly float $scanDurationSeconds = 0.0
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'totalViolations' => $this->totalViolations,
            'totalFilesScanned' => $this->totalFilesScanned,
            'baselinedCount' => $this->baselinedCount,
            'healthScore' => $this->healthScore,
            'bySeverity' => $this->violationCountsBySeverity,
            'byStandard' => $this->violationCountsByStandard,
            'byPrinciple' => $this->violationCountsByPrinciple,
            'complianceScores' => $this->complianceScores,
            'duration' => $this->scanDurationSeconds,
        ];
    }
}
