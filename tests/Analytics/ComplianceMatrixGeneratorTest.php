<?php

namespace YakNet\AccessibilityConsole\Tests\Analytics;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Analytics\ComplianceMatrixGenerator;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ComplianceMatrixGeneratorTest extends TestCase
{
    private ComplianceMatrixGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new ComplianceMatrixGenerator();
    }

    public function testGeneratesMatrixSuccessfully(): void
    {
        $violations = [
            new Violation(
                ruleId: 'WCAG_1_1_1_ALT',
                message: 'Image alt missing',
                severity: Severity::ERROR,
                standard: WCAGStandard::A
            ),
            new Violation(
                ruleId: 'WCAG_2_4_4_LINK_TEXT',
                message: 'Link text empty',
                severity: Severity::ERROR,
                standard: WCAGStandard::A
            ),
        ];

        $matrix = $this->generator->generate($violations, 127);

        $this->assertArrayHasKey('overall_compliance_percentage', $matrix);
        $this->assertArrayHasKey('principles', $matrix);
        $this->assertArrayHasKey('levels', $matrix);

        $this->assertSame(1, $matrix['principles']['Perceivable']['violations']);
        $this->assertSame(1, $matrix['principles']['Operable']['violations']);
        $this->assertSame(0, $matrix['principles']['Understandable']['violations']);
        $this->assertSame('Pass', $matrix['principles']['Understandable']['status']);
    }
}
