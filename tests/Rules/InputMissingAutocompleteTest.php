<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\InputMissingAutocomplete;

class InputMissingAutocompleteTest extends TestCase
{
    public function testFlagsPasswordWithAutocompleteOff(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputMissingAutocomplete());

        $violations = $scanner->scan('<form><input type="password" name="pwd" autocomplete="off"></form>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_3_3_8_ACCESSIBLE_AUTH', $violations[0]->ruleId);
    }

    public function testFlagsPasteBlockingOnInput(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputMissingAutocomplete());

        $violations = $scanner->scan('<form><input type="text" name="pin" onpaste="return false;"></form>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_3_3_8_ACCESSIBLE_AUTH', $violations[0]->ruleId);
    }

    public function testPassesAccessiblePasswordInput(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputMissingAutocomplete());

        $violations = $scanner->scan('<form><input type="password" name="pwd" autocomplete="current-password"></form>');
        $this->assertCount(0, $violations);
    }
}
