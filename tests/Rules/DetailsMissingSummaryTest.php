<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\DetailsMissingSummary;

class DetailsMissingSummaryTest extends TestCase
{
    public function testFlagsDetailsWithoutSummary(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DetailsMissingSummary());

        $violations = $scanner->scan('<details><p>Some hidden details here</p></details>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_DETAILS_SUMMARY', $violations[0]->ruleId);
    }

    public function testFlagsDetailsWithEmptySummary(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DetailsMissingSummary());

        $violations = $scanner->scan('<details><summary></summary><p>Details</p></details>');
        $this->assertCount(1, $violations);
    }

    public function testPassesDetailsWithValidSummary(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DetailsMissingSummary());

        $violations = $scanner->scan('<details><summary>More info</summary><p>Details content</p></details>');
        $this->assertCount(0, $violations);
    }
}
