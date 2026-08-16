<?php

namespace YakNet\AccessibilityConsole\Tests\Analytics;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Analytics\AccessibilityTelemetry;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AccessibilityTelemetryTest extends TestCase
{
    private AccessibilityTelemetry $telemetry;

    protected function setUp(): void
    {
        $this->telemetry = new AccessibilityTelemetry();
    }

    public function testComputesDefectDensityAndRatios(): void
    {
        $violations = [
            new Violation(ruleId: 'R1', message: 'M1', severity: Severity::ERROR, standard: WCAGStandard::A),
            new Violation(ruleId: 'R2', message: 'M2', severity: Severity::WARNING, standard: WCAGStandard::AA),
        ];

        $metrics = $this->telemetry->computeMetrics($violations, 2000);

        $this->assertSame(2, $metrics['total_violations']);
        $this->assertSame(1, $metrics['error_count']);
        $this->assertSame(1, $metrics['warning_count']);
        $this->assertSame(1.0, $metrics['defect_density_per_kloc']); // 2 violations / 2 kloc = 1.0
        $this->assertSame(50.0, $metrics['critical_blockers_ratio']); // 1 / 2 = 50%
    }
}
