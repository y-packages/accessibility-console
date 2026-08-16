<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TableHeadersAttributeValid;

class TableHeadersAttributeValidTest extends TestCase
{
    public function testFlagsInvalidHeaderId(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableHeadersAttributeValid());

        $violations = $scanner->scan('<table><tr><th id="h1">Header 1</th></tr><tr><td headers="nonexistent_h">Cell</td></tr></table>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_TABLE_HEADERS_ATTR', $violations[0]->ruleId);
    }

    public function testPassesValidHeaderId(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableHeadersAttributeValid());

        $violations = $scanner->scan('<table><tr><th id="h1">Header 1</th></tr><tr><td headers="h1">Cell</td></tr></table>');
        $this->assertCount(0, $violations);
    }
}
