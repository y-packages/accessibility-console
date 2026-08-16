<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\NestedInteractive;

class NestedInteractiveTest extends TestCase
{
    public function testFlagsNestedLinkInsideLink(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new NestedInteractive());

        $violations = $scanner->scan('<div><a href="/outer">Outer <a href="/inner">Inner</a></a></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_NESTED_INTERACTIVE', $violations[0]->ruleId);
    }

    public function testFlagsButtonInsideLink(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new NestedInteractive());

        $violations = $scanner->scan('<div><a href="/outer"><button>Button</button></a></div>');
        $this->assertCount(1, $violations);
    }

    public function testPassesNonNestedInteractives(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new NestedInteractive());

        $violations = $scanner->scan('<div><a href="/link">Link</a><button>Button</button></div>');
        $this->assertCount(0, $violations);
    }
}
