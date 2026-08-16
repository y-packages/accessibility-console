<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TargetSizeMinimum;

class TargetSizeMinimumTest extends TestCase
{
    public function testFlagsTooSmallTarget(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TargetSizeMinimum());

        $violations = $scanner->scan('<div><button style="width: 16px; height: 16px;">X</button></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_5_8_TARGET_SIZE', $violations[0]->ruleId);
    }

    public function testPassesAdequateTargetSize(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TargetSizeMinimum());

        $violations = $scanner->scan('<div><button style="width: 24px; height: 24px;">X</button></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesElementsWithoutInlineSize(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TargetSizeMinimum());

        $violations = $scanner->scan('<div><a href="/link">Normal Link</a></div>');
        $this->assertCount(0, $violations);
    }
}
