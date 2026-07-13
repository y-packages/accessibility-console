<?php

namespace YakNet\AccessibilityConsole\Tests\Core;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LocalHealerTest extends TestCase
{
    public function testHealsBlinkTag(): void
    {
        $healer = new LocalHealer();
        $violation = new Violation(
            ruleId: 'WCAG_2_2_2_BLINK',
            message: 'Banned blink tag.',
            severity: Severity::ERROR,
            standard: WCAGStandard::A,
            htmlSnippet: '<blink>Hello</blink>'
        );

        $result = $healer->heal($violation);
        $this->assertNotNull($result);
        $this->assertStringContainsString('FIX: <span style="text-decoration: blink;">Hello</span>', $result);
    }

    public function testHealsAutofocusAttribute(): void
    {
        $healer = new LocalHealer();
        $violation = new Violation(
            ruleId: 'WCAG_2_4_3_AUTOFOCUS',
            message: 'Banned autofocus attribute.',
            severity: Severity::WARNING,
            standard: WCAGStandard::A,
            htmlSnippet: '<input type="text" autofocus />'
        );

        $result = $healer->heal($violation);
        $this->assertNotNull($result);
        $this->assertStringContainsString('FIX: <input type="text" />', $result);
    }
}
