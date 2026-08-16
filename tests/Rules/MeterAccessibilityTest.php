<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\MeterAccessibility;

class MeterAccessibilityTest extends TestCase
{
    public function testFlagsMeterWithoutAccessibleName(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new MeterAccessibility());

        $violations = $scanner->scan('<div><meter value="0.6"></meter></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_METER_ACCESSIBLE', $violations[0]->ruleId);
    }

    public function testPassesMeterWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new MeterAccessibility());

        $violations = $scanner->scan('<div><meter value="0.6" aria-label="Disk Usage"></meter></div>');
        $this->assertCount(0, $violations);
    }
}
