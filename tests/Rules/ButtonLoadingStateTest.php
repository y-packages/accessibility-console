<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ButtonLoadingState;

class ButtonLoadingStateTest extends TestCase
{
    public function testFlagsLoadingButtonWithoutAriaBusy(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ButtonLoadingState());

        $violations = $scanner->scan('<button class="btn is-loading"><span class="spinner"></span> Submit</button>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_BUTTON_LOADING_STATE', $violations[0]->ruleId);
    }

    public function testPassesLoadingButtonWithAriaBusy(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ButtonLoadingState());

        $violations = $scanner->scan('<button class="btn is-loading" aria-busy="true"><span class="spinner"></span> Submit</button>');
        $this->assertCount(0, $violations);
    }
}
