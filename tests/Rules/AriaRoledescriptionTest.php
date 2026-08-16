<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaRoledescription;

class AriaRoledescriptionTest extends TestCase
{
    public function testFlagsEmptyRoledescription(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaRoledescription());

        $violations = $scanner->scan('<div><div role="region" aria-roledescription="">Content</div></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_ROLEDESCRIPTION', $violations[0]->ruleId);
    }

    public function testFlagsRoledescriptionOnPresentation(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaRoledescription());

        $violations = $scanner->scan('<div><div role="presentation" aria-roledescription="slide">Content</div></div>');
        $this->assertCount(1, $violations);
    }

    public function testPassesValidRoledescription(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaRoledescription());

        $violations = $scanner->scan('<div><div role="region" aria-roledescription="slide" aria-label="Slide 1">Slide Content</div></div>');
        $this->assertCount(0, $violations);
    }
}
