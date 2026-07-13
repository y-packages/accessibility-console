<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ScrollableRegionFocusable;

class ScrollableRegionFocusableTest extends TestCase
{
    public function testFlagsScrollableRegionWithoutTabindex(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ScrollableRegionFocusable());

        $violations = $scanner->scan('<div style="overflow: scroll; height: 100px;">Scrollable content</div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_1_1_SCROLLABLE_FOCUS', $violations[0]->ruleId);
    }

    public function testPassesScrollableRegionWithTabindex(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ScrollableRegionFocusable());

        $violations = $scanner->scan('<div style="overflow-y: auto;" tabindex="0">Scrollable content</div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesNonScrollableElement(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ScrollableRegionFocusable());

        $violations = $scanner->scan('<div style="color: blue;">Non-scrollable content</div>');
        $this->assertCount(0, $violations);
    }
}
