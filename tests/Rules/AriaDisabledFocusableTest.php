<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaDisabledFocusable;

class AriaDisabledFocusableTest extends TestCase
{
    public function testFlagsDisabledWithPositiveTabindex(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaDisabledFocusable());

        $violations = $scanner->scan('<div><button disabled tabindex="0">Disabled Button</button></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_DISABLED', $violations[0]->ruleId);
    }

    public function testPassesStandardDisabled(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaDisabledFocusable());

        $violations = $scanner->scan('<div><button disabled>Disabled Button</button></div>');
        $this->assertCount(0, $violations);
    }
}
