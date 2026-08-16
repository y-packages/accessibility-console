<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\EmptyHeading;

class EmptyHeadingTest extends TestCase
{
    public function testFlagsEmptyHeading(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new EmptyHeading());

        $violations = $scanner->scan('<div><h1></h1></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_EMPTY_HEADING', $violations[0]->ruleId);
    }

    public function testFlagsEmptyAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new EmptyHeading());

        $violations = $scanner->scan('<div><h2 aria-label="   "></h2></div>');
        $this->assertCount(1, $violations);
    }

    public function testPassesHeadingWithImageAlt(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new EmptyHeading());

        $violations = $scanner->scan('<div><h1><img src="logo.png" alt="Company Name"></h1></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesHeadingWithSvgTitle(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new EmptyHeading());

        $violations = $scanner->scan('<div><h1><svg><title>Dashboard</title></svg></h1></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesHeadingWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new EmptyHeading());

        $violations = $scanner->scan('<div><h1 aria-label="Accessible Title"></h1></div>');
        $this->assertCount(0, $violations);
    }
}
