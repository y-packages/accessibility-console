<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\FocusableWithoutRole;

class FocusableWithoutRoleTest extends TestCase
{
    public function testFlagsFocusableDivWithoutRole(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FocusableWithoutRole());

        $violations = $scanner->scan('<div tabindex="0">Click me</div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_FOCUSABLE_ROLE', $violations[0]->ruleId);
    }

    public function testPassesFocusableDivWithRole(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FocusableWithoutRole());

        $violations = $scanner->scan('<div tabindex="0" role="button">Click me</div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesNegativeTabindexDiv(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FocusableWithoutRole());

        $violations = $scanner->scan('<div tabindex="-1">Programmatic focus only</div>');
        $this->assertCount(0, $violations);
    }
}
