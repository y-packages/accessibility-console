<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\DuplicateH1;

class DuplicateH1Test extends TestCase
{
    public function testFlagsDuplicateH1(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DuplicateH1());

        $violations = $scanner->scan('<div><h1>Heading 1</h1><h1>Heading 2</h1></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_DUPLICATE_H1', $violations[0]->ruleId);
    }

    public function testPassesSingleH1(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DuplicateH1());

        $violations = $scanner->scan('<div><h1>Heading 1</h1><h2>Heading 2</h2></div>');
        $this->assertCount(0, $violations);
    }
}
