<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\InputTypePasswordVisibility;

class InputTypePasswordVisibilityTest extends TestCase
{
    public function testFlagsPasswordToggleWithoutAccessibleName(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputTypePasswordVisibility());

        $violations = $scanner->scan('<button class="toggle-password"></button>');
        $this->assertGreaterThanOrEqual(1, count($violations));
        $this->assertSame('WCAG_4_1_2_PASSWORD_VISIBILITY_TOGGLE', $violations[0]->ruleId);
    }

    public function testPassesPasswordToggleWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new InputTypePasswordVisibility());

        $violations = $scanner->scan('<button class="toggle-password" aria-label="Şifreyi göster"></button>');
        $this->assertCount(0, $violations);
    }
}
