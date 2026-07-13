<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\SvgAltText;

class SvgAltTextTest extends TestCase
{
    public function testFlagsSvgWithoutAlt(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SvgAltText());

        $violations = $scanner->scan('<div><svg width="10"><circle /></svg></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_1_1_SVG', $violations[0]->ruleId);
    }

    public function testPassesSvgWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SvgAltText());

        $violations = $scanner->scan('<div><svg width="10" aria-label="Company logo"><circle /></svg></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesSvgWithTitle(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SvgAltText());

        $violations = $scanner->scan('<div><svg width="10"><title>Real Title</title><circle /></svg></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesSvgHidden(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SvgAltText());

        $violations = $scanner->scan('<div><svg width="10" aria-hidden="true"><circle /></svg></div>');
        $this->assertCount(0, $violations);
    }
}
