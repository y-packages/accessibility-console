<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AppletBanned;

class AppletBannedTest extends TestCase
{
    public function testFlagsAppletTag(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AppletBanned());

        $violations = $scanner->scan('<div><applet code="MyApplet.class"></applet></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_APPLET_BANNED', $violations[0]->ruleId);
    }

    public function testPassesObjectTag(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AppletBanned());

        $violations = $scanner->scan('<div><object data="movie.mp4">Fallback</object></div>');
        $this->assertCount(0, $violations);
    }
}
