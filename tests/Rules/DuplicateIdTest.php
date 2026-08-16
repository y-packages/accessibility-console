<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\DuplicateId;

class DuplicateIdTest extends TestCase
{
    public function testFlagsDuplicateIds(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DuplicateId());

        $violations = $scanner->scan('<div><div id="card">A</div><div id="card">B</div></div>');
        $this->assertCount(2, $violations);
        $this->assertSame('WCAG_4_1_2_DUPLICATE_ID', $violations[0]->ruleId);
    }

    public function testPassesUniqueIds(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DuplicateId());

        $violations = $scanner->scan('<div><div id="card1">A</div><div id="card2">B</div></div>');
        $this->assertCount(0, $violations);
    }
}
