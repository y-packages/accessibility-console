<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaExpandedState;

class AriaExpandedStateTest extends TestCase
{
    public function testFlagsDropdownToggleWithoutAriaExpanded(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaExpandedState());

        $violations = $scanner->scan('<button class="dropdown-toggle" data-bs-toggle="dropdown">Menu</button>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_EXPANDED', $violations[0]->ruleId);
    }

    public function testPassesDropdownToggleWithAriaExpanded(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaExpandedState());

        $violations = $scanner->scan('<button class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Menu</button>');
        $this->assertCount(0, $violations);
    }
}
