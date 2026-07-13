<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TableSummaryBanned;

class TableSummaryBannedTest extends TestCase
{
    public function testFlagsTableWithSummaryAttribute(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableSummaryBanned());

        $violations = $scanner->scan('<div><table summary="This table lists details."><tr><td>Data</td></tr></table></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_TABLE_SUMMARY', $violations[0]->ruleId);
    }

    public function testPassesTableWithoutSummaryAttribute(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableSummaryBanned());

        $violations = $scanner->scan('<div><table><caption>Details</caption><tr><td>Data</td></tr></table></div>');
        $this->assertCount(0, $violations);
    }
}
