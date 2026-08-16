<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\FormLabel;

class FormLabelTest extends TestCase
{
    public function testFlagsInputWithoutLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FormLabel());

        $violations = $scanner->scan('<div><input type="text" name="username"></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_LABEL', $violations[0]->ruleId);
    }

    public function testFlagsEmptyAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FormLabel());

        $violations = $scanner->scan('<div><input type="text" name="username" aria-label="   "></div>');
        $this->assertCount(1, $violations);
    }

    public function testPassesInputWithAssociatedLabelFor(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FormLabel());

        $violations = $scanner->scan('<div><label for="usr">Username</label><input type="text" id="usr" name="username"></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesInputWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FormLabel());

        $violations = $scanner->scan('<div><input type="text" name="username" aria-label="Username"></div>');
        $this->assertCount(0, $violations);
    }
}
