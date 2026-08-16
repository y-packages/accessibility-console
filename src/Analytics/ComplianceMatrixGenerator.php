<?php

namespace YakNet\AccessibilityConsole\Analytics;

use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ComplianceMatrixGenerator
{
    /** @var array<int|string, string> */
    private static array $principleMap = [
        1 => 'Perceivable',
        2 => 'Operable',
        3 => 'Understandable',
        4 => 'Robust',
    ];

    /**
     * Generate WCAG 2.1 & 2.2 Compliance Matrix from a list of violations and total rules tested.
     *
     * @param Violation[] $violations
     * @param int $totalActiveRules
     * @return array{
     *     overall_compliance_percentage: float,
     *     principles: array<string, array{violations: int, status: string}>,
     *     levels: array<string, array{violations: int, percentage: float}>
     * }
     */
    public function generate(array $violations, int $totalActiveRules = 127): array
    {
        $principles = [
            'Perceivable' => ['violations' => 0, 'status' => 'Pass'],
            'Operable' => ['violations' => 0, 'status' => 'Pass'],
            'Understandable' => ['violations' => 0, 'status' => 'Pass'],
            'Robust' => ['violations' => 0, 'status' => 'Pass'],
        ];

        /** @var array<string, array{violations: int, percentage: float}> $levelCounts */
        $levelCounts = [
            'A' => ['violations' => 0, 'percentage' => 100.0],
            'AA' => ['violations' => 0, 'percentage' => 100.0],
            'AAA' => ['violations' => 0, 'percentage' => 100.0],
        ];

        foreach ($violations as $v) {
            // Determine principle from rule ID or WCAG code (e.g. WCAG_1_... => Perceivable)
            $principleNumber = 4;
            if (preg_match('/WCAG_([1-4])_/', $v->ruleId, $m)) {
                $principleNumber = (int)$m[1];
            }
            $pName = self::$principleMap[$principleNumber] ?? 'Robust';
            $principles[$pName]['violations']++;
            $principles[$pName]['status'] = 'Needs Review';

            // Level breakdown
            $stdVal = $v->standard->value;
            if (array_key_exists($stdVal, $levelCounts)) {
                $levelCounts[$stdVal]['violations']++;
            }
        }

        $totalViolations = count($violations);
        $overallScore = ($totalActiveRules > 0)
            ? max(0.0, min(100.0, round((1.0 - ($totalViolations / ($totalActiveRules * 2))) * 100, 1)))
            : 100.0;

        foreach ($levelCounts as $lvl => &$data) {
            $data['percentage'] = max(0.0, min(100.0, round((1.0 - ($data['violations'] / max(1, $totalActiveRules))) * 100, 1)));
        }

        return [
            'overall_compliance_percentage' => $overallScore,
            'principles' => $principles,
            'levels' => $levelCounts,
        ];
    }
}
