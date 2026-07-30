<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ListItemParent;

class ListItemParentTest extends TestCase
{
    public function testFlagsOrphanLiElement(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ListItemParent());

        $violations = $scanner->scan('<div><li>Orphan Item</li></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_LIST_ITEM_PARENT', $violations[0]->ruleId);
    }

    public function testPassesLiInsideUl(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ListItemParent());

        $violations = $scanner->scan('<ul><li>Valid Item</li></ul>');
        $this->assertCount(0, $violations);
    }
}
