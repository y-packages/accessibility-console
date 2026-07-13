<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ImageAltLong;

class ImageAltLongTest extends TestCase
{
    public function testFlagsTooLongAltText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageAltLong());

        $longAlt = str_repeat("a", 151);
        $violations = $scanner->scan('<div><img src="pic.png" alt="' . $longAlt . '" /></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_1_1_ALT_LONG', $violations[0]->ruleId);
    }

    public function testPassesNormalAltText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageAltLong());

        $violations = $scanner->scan('<div><img src="pic.png" alt="Short description" /></div>');
        $this->assertCount(0, $violations);
    }
}
