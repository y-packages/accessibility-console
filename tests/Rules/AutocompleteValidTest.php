<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AutocompleteValid;

class AutocompleteValidTest extends TestCase
{
    public function testFlagsMissingAutocomplete(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AutocompleteValid());

        $violations = $scanner->scan('<div><input type="email" name="user_email" /></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_5_AUTOCOMPLETE', $violations[0]->ruleId);
    }

    public function testPassesWithAutocomplete(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AutocompleteValid());

        $violations = $scanner->scan('<div><input type="email" name="user_email" autocomplete="email" /></div>');
        $this->assertCount(0, $violations);
    }

    public function testSkipsNonPersonalInputs(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AutocompleteValid());

        $violations = $scanner->scan('<div><input type="checkbox" name="agree" /><input type="submit" /></div>');
        $this->assertCount(0, $violations);
    }
}
