<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TableHeaderNotEmpty;

class TableHeaderNotEmptyTest extends TestCase
{
    public function testFlagsEmptyTableHeader(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableHeaderNotEmpty());

        $violations = $scanner->scan('<table><tr><th></th><td>Value</td></tr></table>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_TH_NOT_EMPTY', $violations[0]->ruleId);
    }

    public function testPassesTableHeaderWithText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableHeaderNotEmpty());

        $violations = $scanner->scan('<table><tr><th>Name</th><td>John</td></tr></table>');
        $this->assertCount(0, $violations);
    }

    public function testPassesTableHeaderWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TableHeaderNotEmpty());

        $violations = $scanner->scan('<table><tr><th aria-label="Action buttons"></th><td>Edit</td></tr></table>');
        $this->assertCount(0, $violations);
    }
}
