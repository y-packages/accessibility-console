<?php

namespace YakNet\AccessibilityConsole\Tests\Core;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AccessibilityScoreCalculatorTest extends TestCase
{
    public function testScoreCalculationWithNoViolations(): void
    {
        $metrics = AccessibilityScoreCalculator::calculate([]);
        $this->assertSame(100, $metrics['score']);
    }

    public function testScoreCalculationDeductions(): void
    {
        $violations = [
            new Violation('WCAG_1_1_1_ALT', 'Img alt missing', Severity::ERROR, WCAGStandard::A, '<img>'), // -8
            new Violation('WCAG_2_4_3_AUTOFOCUS', 'Autofocus usage', Severity::WARNING, WCAGStandard::A, '<input autofocus>') // -3
        ];

        $metrics = AccessibilityScoreCalculator::calculate($violations);
        $this->assertSame(89, $metrics['score']); // 100 - 8 - 3 = 89
    }
}
