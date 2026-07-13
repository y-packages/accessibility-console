<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ImageAltPlaceholder;

class ImageAltPlaceholderTest extends TestCase
{
    public function testFlagsPlaceholderAltText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageAltPlaceholder());

        $violations = $scanner->scan('<div><img src="logo.png" alt="logo" /></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_1_1_ALT_PLACEHOLDER', $violations[0]->ruleId);
    }

    public function testFlagsFilenameAltText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageAltPlaceholder());

        $violations = $scanner->scan('<div><img src="banner.jpg" alt="header_banner.jpg" /></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_1_1_ALT_PLACEHOLDER', $violations[0]->ruleId);
    }

    public function testPassesDescriptiveAltText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageAltPlaceholder());

        $violations = $scanner->scan('<div><img src="banner.jpg" alt="YakNet Static Analysis Console Banner Image" /></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesEmptyAlt(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ImageAltPlaceholder());

        $violations = $scanner->scan('<div><img src="decoration.jpg" alt="" /></div>');
        $this->assertCount(0, $violations);
    }
}
