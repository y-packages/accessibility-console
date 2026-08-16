<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\InputAutocompleteAppropriate;

class InputAutocompleteAppropriateTest extends TestCase
{
    public function testFlagsInappropriateAutocompleteType(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputAutocompleteAppropriate());

        $html = '<form><input type="number" autocomplete="email"></form>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_5_AUTOCOMPLETE_APPROPRIATE', $violations[0]->ruleId);
    }

    public function testPassesAppropriateAutocompleteType(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputAutocompleteAppropriate());

        $html = '<form><input type="email" autocomplete="email"></form>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }
}
