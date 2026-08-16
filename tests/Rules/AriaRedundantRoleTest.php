<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaRedundantRole;

class AriaRedundantRoleTest extends TestCase
{
    public function testFlagsRedundantRoleOnNav(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaRedundantRole());

        $violations = $scanner->scan('<div><nav role="navigation"><a href="/">Home</a></nav></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_REDUNDANT_ROLE', $violations[0]->ruleId);
    }

    public function testPassesNavWithoutRedundantRole(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaRedundantRole());

        $violations = $scanner->scan('<div><nav><a href="/">Home</a></nav></div>');
        $this->assertCount(0, $violations);
    }
}
