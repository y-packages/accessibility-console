<?php

namespace YakNet\AccessibilityConsole\Tests\Reporting;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Reporting\PdfReportGenerator;

class PdfReportGeneratorTest extends TestCase
{
    public function testGeneratesPdfBinaryContent(): void
    {
        $scanResult = [
            'score' => 95,
            'violations_count' => 1,
            'violations' => [
                [
                    'ruleId' => 'WCAG_1_1_1_IMG_ALT',
                    'description' => 'Image missing alt text',
                    'htmlSnippet' => '<img src="test.jpg">',
                    'suggestion' => 'Add alt="Description"',
                    'standard' => 'WCAG 2.1 AA',
                    'severity' => 'ERROR'
                ]
            ]
        ];

        $pdfBinary = PdfReportGenerator::generatePdf($scanResult, 'https://example.com');
        $this->assertNotEmpty($pdfBinary);
        $this->assertStringStartsWith('%PDF-', $pdfBinary);
    }
}
