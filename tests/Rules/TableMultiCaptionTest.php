<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TableMultiCaption;

class TableMultiCaptionTest extends TestCase
{
    public function testFlagsMultipleCaptionsInTable(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableMultiCaption());

        $violations = $scanner->scan('<table><caption>Caption 1</caption><caption>Caption 2</caption><tr><td>Data</td></tr></table>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_TABLE_MULTI_CAPTION', $violations[0]->ruleId);
    }

    public function testPassesSingleCaptionTable(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableMultiCaption());

        $violations = $scanner->scan('<table><caption>Single Caption</caption><tr><td>Data</td></tr></table>');
        $this->assertCount(0, $violations);
    }
}
