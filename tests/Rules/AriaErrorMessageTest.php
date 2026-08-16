<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaErrorMessage;

class AriaErrorMessageTest extends TestCase
{
    public function testFlagsMissingTargetElement(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaErrorMessage());

        $violations = $scanner->scan('<div><input id="email" aria-invalid="true" aria-errormessage="nonexistent_err"></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_3_3_1_ARIA_ERRORMESSAGE', $violations[0]->ruleId);
    }

    public function testFlagsMissingAriaInvalid(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaErrorMessage());

        $violations = $scanner->scan('<div><input id="email" aria-errormessage="email_err"><span id="email_err">Error</span></div>');
        $this->assertCount(1, $violations);
    }

    public function testPassesValidErrorMessageSetup(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaErrorMessage());

        $violations = $scanner->scan('<div><input id="email" aria-invalid="true" aria-errormessage="email_err"><span id="email_err">Invalid Email Address</span></div>');
        $this->assertCount(0, $violations);
    }
}
