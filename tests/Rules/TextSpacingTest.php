<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TextSpacing;

class TextSpacingTest extends TestCase
{
    public function testFlagsConstrainedLineHeight(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TextSpacing());

        $html = '<p style="line-height: 1; font-size: 16px;">This text has insufficient line height.</p>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_4_12_TEXT_SPACING', $violations[0]->ruleId);
    }

    public function testPassesAdequateLineHeight(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TextSpacing());

        $html = '<p style="line-height: 1.6; font-size: 16px;">Proper line height content.</p>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }

    public function testFlagsFixedHeightWithOverflowHiddenOnText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TextSpacing());

        $html = '<div style="height: 30px; overflow: hidden;"><p>This is a long paragraph that might be clipped when spacing is enlarged by the user in accessible mode.</p></div>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
    }
}
