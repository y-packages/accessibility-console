<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Compliance;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Compliance\VpatReportGenerator;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class VpatReportGeneratorTest extends TestCase
{
    public function testGeneratesVpatDataAndAllFormats(): void
    {
        $violations = [
            new Violation('WCAG_1_1_1_ALT', 'Missing alt', Severity::ERROR, WCAGStandard::A),
            new Violation('WCAG_2_5_8_TARGET_SIZE', 'Small target', Severity::WARNING, WCAGStandard::AA),
        ];

        $generator = new VpatReportGenerator();
        $data = $generator->buildReportData($violations, 'YakNet Portal', '2.0.0', 'YakNet QA');

        $this->assertSame('YakNet Portal', $data['product_name']);
        $this->assertSame('Does Not Support', $data['criteria']['1.1.1']['conformance']);
        $this->assertSame('Partially Supports', $data['criteria']['2.5.8']['conformance']);
        $this->assertSame('Supports', $data['criteria']['3.1.1']['conformance']);
        $this->assertSame('Not Evaluated', $data['criteria']['1.4.10']['conformance']);

        $markdown = $generator->renderMarkdown($violations, 'YakNet Portal', '2.0.0');
        $this->assertStringContainsString('# Accessibility Conformance Report (VPAT 2.5)', $markdown);
        $this->assertStringContainsString('Does Not Support', $markdown);
        $this->assertStringContainsString('YakNet Accessibility Console', $markdown);

        $html = $generator->renderHtml($violations, 'YakNet Portal', '2.0.0');
        $this->assertStringContainsString('<title>VPAT 2.5 Accessibility Conformance Report', $html);
        $this->assertStringContainsString('Partially Supports', $html);

        $json = $generator->renderJson($violations, 'YakNet Portal', '2.0.0');
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertSame(2, $decoded['total_violations']);
    }
}
