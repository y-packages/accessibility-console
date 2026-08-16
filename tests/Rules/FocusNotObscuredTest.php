<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\FocusNotObscured;

class FocusNotObscuredTest extends TestCase
{
    public function testFlagsFixedFullWidthTopBarWithoutScrollPadding(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FocusNotObscured());

        $html = '<div><header style="position: fixed; top: 0; left: 0; width: 100%; height: 60px;">Navbar</header></div>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_4_11_FOCUS_NOT_OBSCURED', $violations[0]->ruleId);
    }

    public function testPassesStaticElements(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FocusNotObscured());

        $html = '<div><header style="width: 100%; height: 60px;">Navbar</header></div>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }
}
