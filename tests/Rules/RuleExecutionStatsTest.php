<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Rules\RuleExecutionStats;

class RuleExecutionStatsTest extends TestCase
{
    public function testRecordAndRetrieveStats(): void
    {
        $stats = new RuleExecutionStats();

        $stats->record('WCAG_1_1_1_IMG_ALT', 2.5, true);
        $stats->record('WCAG_1_1_1_IMG_ALT', 1.5, false);
        $stats->record('WCAG_1_4_3_CONTRAST', 10.0, true);

        $all = $stats->getAllStats();
        $this->assertSame(2, $all['WCAG_1_1_1_IMG_ALT']['invocations']);
        $this->assertSame(4.0, $all['WCAG_1_1_1_IMG_ALT']['total_time_ms']);
        $this->assertSame(1, $all['WCAG_1_1_1_IMG_ALT']['violations']);

        $slowest = $stats->getSlowestRules(1);
        $this->assertArrayHasKey('WCAG_1_4_3_CONTRAST', $slowest);
        $this->assertSame(10.0, $slowest['WCAG_1_4_3_CONTRAST']['total_time_ms']);
    }

    public function testResetStats(): void
    {
        $stats = new RuleExecutionStats();
        $stats->record('WCAG_1_1_1_IMG_ALT', 5.0, true);
        $this->assertNotEmpty($stats->getAllStats());

        $stats->reset();
        $this->assertEmpty($stats->getAllStats());
    }
}
