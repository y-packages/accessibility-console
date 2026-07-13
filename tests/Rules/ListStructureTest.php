<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ListStructure;

class ListStructureTest extends TestCase
{
    public function testFlagsInvalidDirectChildrenInList(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ListStructure());

        $violations = $scanner->scan('<ul><div>Invalid</div><li>Valid</li></ul>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_LIST_STRUCTURE', $violations[0]->ruleId);
    }

    public function testPassesValidListStructure(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ListStructure());

        $violations = $scanner->scan('<ul><li>Valid 1</li><li>Valid 2</li><script>alert(1);</script></ul>');
        $this->assertCount(0, $violations);
    }
}
