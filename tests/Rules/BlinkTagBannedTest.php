<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\BlinkTagBanned;

class BlinkTagBannedTest extends TestCase
{
    public function testFlagsBlinkTag(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new BlinkTagBanned());

        $violations = $scanner->scan('<div><blink>Blinking text!</blink></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_2_2_BLINK', $violations[0]->ruleId);
    }

    public function testPassesNormalSpan(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new BlinkTagBanned());

        $violations = $scanner->scan('<div><span>Normal text</span></div>');
        $this->assertCount(0, $violations);
    }
}
