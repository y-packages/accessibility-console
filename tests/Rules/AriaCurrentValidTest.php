<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaCurrentValid;

class AriaCurrentValidTest extends TestCase
{
    public function testFlagsInvalidAriaCurrent(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaCurrentValid());

        $violations = $scanner->scan('<div><a href="/home" aria-current="active">Home</a></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_CURRENT', $violations[0]->ruleId);
    }

    public function testPassesValidAriaCurrent(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaCurrentValid());

        $violations = $scanner->scan('<div><a href="/home" aria-current="page">Home</a></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesBooleanAriaCurrent(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaCurrentValid());

        $violations = $scanner->scan('<div><a href="/step2" aria-current="true">Step 2</a></div>');
        $this->assertCount(0, $violations);
    }
}
