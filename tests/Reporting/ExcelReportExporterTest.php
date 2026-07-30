<?php

namespace YakNet\AccessibilityConsole\Tests\Reporting;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Reporting\ExcelReportExporter;

class ExcelReportExporterTest extends TestCase
{
    public function testExportsExcelBinaryContent(): void
    {
        $scanResult = [
            'score' => 90,
            'violations_count' => 1,
            'violations' => [
                [
                    'ruleId' => 'WCAG_3_1_1_LANG',
                    'description' => 'Missing lang attribute',
                    'htmlSnippet' => '<html>',
                    'suggestion' => 'Add lang="tr"',
                    'standard' => 'WCAG 2.1 A',
                    'severity' => 'ERROR'
                ]
            ]
        ];

        $excelBinary = ExcelReportExporter::exportExcel($scanResult, 'https://example.com');
        $this->assertNotEmpty($excelBinary);
    }
}
