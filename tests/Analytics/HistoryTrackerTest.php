<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Analytics;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Analytics\HistoryTracker;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HistoryTrackerTest extends TestCase
{
    public function testRecordsSnapshotsAndComputesImprovingTrend(): void
    {
        $historyFile = sys_get_temp_dir() . '/yaknet_history_' . uniqid() . '.json';
        $tracker = new HistoryTracker();

        try {
            $tracker->record([
                new Violation('WCAG_1_1_1_ALT', 'Missing alt', Severity::ERROR, WCAGStandard::A),
                new Violation('WCAG_4_1_2_BUTTON', 'Empty button', Severity::ERROR, WCAGStandard::A),
            ], $historyFile, 'views', 'wcag22-aa');

            $tracker->record([
                new Violation('WCAG_1_1_1_ALT', 'Missing alt', Severity::ERROR, WCAGStandard::A),
            ], $historyFile, 'views', 'wcag22-aa');

            $entries = $tracker->load($historyFile);
            $this->assertCount(2, $entries);

            $trend = $tracker->computeTrend($entries);
            $this->assertSame(2, $trend['runs']);
            $this->assertSame('improving', $trend['trend']);
            $this->assertGreaterThan(0, $trend['score_delta']);
        } finally {
            @unlink($historyFile);
        }
    }

    public function testComparesTwoReportsForRegressionsAndResolutions(): void
    {
        $tracker = new HistoryTracker();

        $before = [
            ['ruleId' => 'WCAG_1_1_1_ALT', 'severity' => 'error', 'htmlSnippet' => '<img src="1.png">', 'location' => ['file' => 'a.html', 'line' => 5]],
            ['ruleId' => 'WCAG_2_4_4_LINK_TEXT', 'severity' => 'error', 'htmlSnippet' => '<a href="/"></a>', 'location' => ['file' => 'a.html', 'line' => 9]],
        ];

        $after = [
            ['ruleId' => 'WCAG_1_1_1_ALT', 'severity' => 'error', 'htmlSnippet' => '<img src="1.png">', 'location' => ['file' => 'a.html', 'line' => 5]],
            ['ruleId' => 'WCAG_3_2_2_BUTTON_TYPE', 'severity' => 'warning', 'htmlSnippet' => '<button>Go</button>', 'location' => ['file' => 'b.html', 'line' => 12]],
        ];

        $diff = $tracker->compareReports($before, $after);

        $this->assertSame(1, $diff['unchanged_count']);
        $this->assertCount(1, $diff['new_violations']);
        $this->assertCount(1, $diff['resolved_violations']);
        $this->assertSame('WCAG_3_2_2_BUTTON_TYPE', $diff['new_violations'][0]['ruleId']);
        $this->assertSame('WCAG_2_4_4_LINK_TEXT', $diff['resolved_violations'][0]['ruleId']);
    }
}
