<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ImageDimensionsAndScale;

class ImageDimensionsAndScaleTest extends TestCase
{
    public function testFlagsMissingWidthAttribute(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageDimensionsAndScale());

        $violations = $scanner->scan('<img src="test.jpg" alt="Test image">');
        $this->assertGreaterThanOrEqual(1, count($violations));
        $this->assertSame('WCAG_1_4_4_IMAGE_DIMENSIONS_SCALE', $violations[0]->ruleId);
    }

    public function testFlagsSmallInteractiveTouchTarget(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageDimensionsAndScale());

        $violations = $scanner->scan('<a href="/"><img src="icon.png" alt="Home" width="12" height="12"></a>');
        $this->assertGreaterThanOrEqual(1, count($violations));
    }

    public function testPassesValidImageDimensions(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageDimensionsAndScale());

        $violations = $scanner->scan('<img src="test.jpg" alt="Test" width="300" height="200">');
        $this->assertCount(0, $violations);
    }
}
