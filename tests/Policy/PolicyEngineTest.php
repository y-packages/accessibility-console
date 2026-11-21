<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Policy;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;
use YakNet\AccessibilityConsole\Policy\PolicyEngine;

class PolicyEngineTest extends TestCase
{
    public function testPolicyPassesWhenWithinThresholds(): void
    {
        $engine = new PolicyEngine([
            'min_score' => 90,
            'max_errors' => 0,
            'max_warnings' => 2,
            'required_standards' => ['A'],
        ]);

        $violations = [
            new Violation('WCAG_2_5_8_TARGET_SIZE', 'Small button', Severity::WARNING, WCAGStandard::AA),
        ];

        $result = $engine->evaluate($violations);
        $this->assertTrue($result->passed);
        $this->assertEmpty($result->failures);
    }

    public function testPolicyEnforcesAllGovernanceGatesAndSeverityOverrides(): void
    {
        $engine = new PolicyEngine([
            'min_score' => 95,
            'max_critical' => 0,
            'required_standards' => ['AA'],
            'banned_rules' => ['WCAG_1_1_1_ALT'],
            'principle_limits' => ['Perceivable' => 0],
            'severity_overrides' => [
                'WCAG_2_5_8_TARGET_SIZE' => 'critical',
            ],
        ]);

        $v1 = new Violation('WCAG_1_1_1_ALT', 'Missing alt', Severity::ERROR, WCAGStandard::A);
        $v2 = new Violation('WCAG_2_5_8_TARGET_SIZE', 'Small target', Severity::WARNING, WCAGStandard::AA);

        $result = $engine->evaluate([$v1, $v2]);

        $this->assertFalse($result->passed);
        $this->assertSame(Severity::CRITICAL, $v2->severity);
        $this->assertGreaterThanOrEqual(4, count($result->failures));
    }
}
