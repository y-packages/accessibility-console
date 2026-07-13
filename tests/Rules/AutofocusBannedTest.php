<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AutofocusBanned;

class AutofocusBannedTest extends TestCase
{
    public function testFlagsAutofocusAttribute(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AutofocusBanned());

        $violations = $scanner->scan('<div><input type="text" name="search" autofocus /></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_4_3_AUTOFOCUS', $violations[0]->ruleId);
    }

    public function testPassesNormalInput(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AutofocusBanned());

        $violations = $scanner->scan('<div><input type="text" name="search" /></div>');
        $this->assertCount(0, $violations);
    }
}
