<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ColorBlindnessContrast;

class ColorBlindnessContrastTest extends TestCase
{
    public function testFlagsLowContrastUnderColorBlindnessSimulation(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ColorBlindnessContrast());

        // Red text #ff0000 on Green background #00ff00 (Classic red/green colorblindness issue)
        $violations = $scanner->scan('<div style="color: #ff0000; background-color: #00ff00;">Red on Green text</div>');
        $this->assertGreaterThanOrEqual(1, count($violations));
        $this->assertSame('WCAG_1_4_1_COLOR_BLINDNESS_CONTRAST', $violations[0]->ruleId);
    }

    public function testPassesHighContrastUnderColorBlindnessSimulation(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ColorBlindnessContrast());

        // Black text #000000 on White background #ffffff
        $violations = $scanner->scan('<div style="color: #000000; background-color: #ffffff;">Black on White text</div>');
        $this->assertCount(0, $violations);
    }
}
