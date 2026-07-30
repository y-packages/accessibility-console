<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TableNestedBanned;

class TableNestedBannedTest extends TestCase
{
    public function testFlagsNestedTable(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableNestedBanned());

        $violations = $scanner->scan('<table><tr><td><table><tr><td>Nested table</td></tr></table></td></tr></table>');
        $this->assertGreaterThanOrEqual(1, count($violations));
        $this->assertSame('WCAG_1_3_1_TABLE_NESTED_BANNED', $violations[0]->ruleId);
    }

    public function testPassesSingleTable(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableNestedBanned());

        $violations = $scanner->scan('<table><tr><td>Single table data</td></tr></table>');
        $this->assertCount(0, $violations);
    }
}
