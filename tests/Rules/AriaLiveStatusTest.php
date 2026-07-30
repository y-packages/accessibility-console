<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaLiveStatus;

class AriaLiveStatusTest extends TestCase
{
    public function testFlagsToastContainerWithoutAriaLive(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaLiveStatus());

        $violations = $scanner->scan('<div class="toast">Saving changes...</div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_3_STATUS_MESSAGES', $violations[0]->ruleId);
    }

    public function testPassesToastWithAriaLive(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaLiveStatus());

        $violations = $scanner->scan('<div class="toast" aria-live="polite">Saving changes...</div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesToastWithRoleStatus(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaLiveStatus());

        $violations = $scanner->scan('<div class="toast" role="status">Saving changes...</div>');
        $this->assertCount(0, $violations);
    }
}
