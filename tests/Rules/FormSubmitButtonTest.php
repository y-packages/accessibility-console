<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\FormSubmitButton;

class FormSubmitButtonTest extends TestCase
{
    public function testFlagsFormWithoutSubmitButton(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FormSubmitButton());

        $violations = $scanner->scan('<form><input type="text" name="name" /></form>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_3_2_2_FORM_SUBMIT', $violations[0]->ruleId);
    }

    public function testPassesFormWithButtonSubmit(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FormSubmitButton());

        $violations = $scanner->scan('<form><input type="text" name="name" /><button>Submit</button></form>');
        $this->assertCount(0, $violations);
    }

    public function testPassesFormWithInputSubmit(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FormSubmitButton());

        $violations = $scanner->scan('<form><input type="text" name="name" /><input type="submit" value="Go" /></form>');
        $this->assertCount(0, $violations);
    }
}
