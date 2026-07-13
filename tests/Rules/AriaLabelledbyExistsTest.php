<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaLabelledbyExists;

class AriaLabelledbyExistsTest extends TestCase
{
    public function testFlagsMissingAriaLabelTarget(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaLabelledbyExists());

        $violations = $scanner->scan('<div><button aria-labelledby="non_existent_id">Submit</button></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_LABELLEDBY', $violations[0]->ruleId);
    }

    public function testPassesExistingAriaLabelTarget(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaLabelledbyExists());

        $violations = $scanner->scan('<div><div id="real_id">Label</div><button aria-labelledby="real_id">Submit</button></div>');
        $this->assertCount(0, $violations);
    }
}
