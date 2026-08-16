<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\HeadingOrder;

class HeadingOrderTest extends TestCase
{
    public function testFlagsSkippedHeadingLevel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new HeadingOrder());

        $violations = $scanner->scan('<div><h1>Main Heading</h1><h3>Skipped Subheading</h3></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_HEADING', $violations[0]->ruleId);
    }

    public function testPassesSequentialHeadings(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new HeadingOrder());

        $violations = $scanner->scan('<div><h1>Main Heading</h1><h2>Section</h2><h3>Sub-section</h3></div>');
        $this->assertCount(0, $violations);
    }

    public function testIsStatelessAcrossMultipleScans(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new HeadingOrder());

        // First scan ends with H3
        $scanner->scan('<div><h1>H1</h1><h2>H2</h2><h3>H3</h3></div>');

        // Second scan starts with H1, should not flag jump from previous scan's H3
        $violations = $scanner->scan('<div><h1>Fresh Document H1</h1><h2>H2</h2></div>');
        $this->assertCount(0, $violations);
    }
}
