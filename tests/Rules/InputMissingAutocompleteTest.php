<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\InputMissingAutocomplete;

class InputMissingAutocompleteTest extends TestCase
{
    public function testFlagsPersonalDataInputWithoutAutocomplete(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputMissingAutocomplete());

        $violations = $scanner->scan('<form><input type="email" name="user_email"></form>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_5_INPUT_AUTOCOMPLETE', $violations[0]->ruleId);
    }

    public function testPassesPersonalDataInputWithAutocomplete(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputMissingAutocomplete());

        $violations = $scanner->scan('<form><input type="email" name="user_email" autocomplete="email"></form>');
        $this->assertCount(0, $violations);
    }
}
