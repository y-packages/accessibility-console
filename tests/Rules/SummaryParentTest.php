<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\SummaryParent;

class SummaryParentTest extends TestCase
{
    public function testFlagsOrphanSummary(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SummaryParent());

        $violations = $scanner->scan('<div><summary>Summary text</summary></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_SUMMARY_PARENT', $violations[0]->ruleId);
    }

    public function testPassesSummaryInDetails(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SummaryParent());

        $violations = $scanner->scan('<details><summary>Summary text</summary><p>More details</p></details>');
        $this->assertCount(0, $violations);
    }
}
