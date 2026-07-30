<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaPressedToggle;

class AriaPressedToggleTest extends TestCase
{
    public function testFlagsToggleButtonWithoutAriaPressed(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaPressedToggle());

        $violations = $scanner->scan('<button class="btn-toggle">Mute</button>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_TOGGLE_BUTTON_STATE', $violations[0]->ruleId);
    }

    public function testPassesToggleButtonWithAriaPressed(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaPressedToggle());

        $violations = $scanner->scan('<button class="btn-toggle" aria-pressed="false">Mute</button>');
        $this->assertCount(0, $violations);
    }
}
