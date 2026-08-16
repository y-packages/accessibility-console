<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaBrailleEquivalent;

class AriaBrailleEquivalentTest extends TestCase
{
    public function testFlagsBrailleLabelWithoutStandardName(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaBrailleEquivalent());

        $violations = $scanner->scan('<div><button aria-braillelabel="btn"></button></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_BRAILLE', $violations[0]->ruleId);
    }

    public function testPassesBrailleLabelWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaBrailleEquivalent());

        $violations = $scanner->scan('<div><button aria-braillelabel="btn" aria-label="Submit Form">Submit</button></div>');
        $this->assertCount(0, $violations);
    }
}
