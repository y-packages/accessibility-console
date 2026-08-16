<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ButtonNestedInteractive;

class ButtonNestedInteractiveTest extends TestCase
{
    public function testFlagsNestedAnchorInsideButton(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ButtonNestedInteractive());

        $violations = $scanner->scan('<div><button><a href="/cart">Cart</a></button></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_BUTTON_NESTED_INTERACTIVE', $violations[0]->ruleId);
    }

    public function testFlagsNestedButtonInsideButton(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ButtonNestedInteractive());

        $violations = $scanner->scan('<div><button><button>Sub action</button></button></div>');
        $this->assertCount(1, $violations);
    }

    public function testPassesStandardButton(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ButtonNestedInteractive());

        $violations = $scanner->scan('<div><button><span>Standard Button</span></button></div>');
        $this->assertCount(0, $violations);
    }
}
