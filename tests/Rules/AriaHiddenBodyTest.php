<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaHiddenBody;

class AriaHiddenBodyTest extends TestCase
{
    public function testFlagsAriaHiddenBody(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaHiddenBody());

        $violations = $scanner->scan('<body aria-hidden="true"><div>Content</div></body>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_HIDDEN_BODY', $violations[0]->ruleId);
    }

    public function testPassesNormalBody(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaHiddenBody());

        $violations = $scanner->scan('<body><div>Content</div></body>');
        $this->assertCount(0, $violations);
    }
}
