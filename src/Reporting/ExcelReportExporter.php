<?php

namespace YakNet\AccessibilityConsole\Reporting;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelReportExporter
{
    /**
     * Export WCAG audit results as an Excel (.xlsx) workbook.
     *
     * @param array<string, mixed> $scanResult
     * @param string $targetUrl
     * @return string Binary Excel content
     */
    public static function exportExcel(array $scanResult, string $targetUrl): string
    {
        if (!class_exists(Spreadsheet::class)) {
            throw new \RuntimeException('To export Excel audit reports, please install phpspreadsheet via: composer require phpoffice/phpspreadsheet');
        }

        /** @var int $score */
        $score = is_numeric($scanResult['score'] ?? null) ? intval($scanResult['score']) : 0;

        /** @var array<int, array<string, mixed>> $violations */
        $violations = is_array($scanResult['violations'] ?? null) ? $scanResult['violations'] : [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Erişilebilirlik Raporu');

        // Headers
        $sheet->setCellValue('A1', 'YakNet Accessibility Console - WCAG 2.1 Audit Export');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Hedef URL / Dizin:');
        $sheet->setCellValue('B3', $targetUrl);
        $sheet->setCellValue('A4', 'WCAG Skoru:');
        $sheet->setCellValue('B4', $score . ' / 100');
        $sheet->setCellValue('A5', 'İhlal Sayısı:');
        $sheet->setCellValue('B5', count($violations));

        // Table Header
        $row = 7;
        $headers = ['#', 'Kural ID', 'Standart', 'Ciddiyet', 'İhlal Açıklaması', 'Kod Snippet'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F'];

        foreach ($headers as $i => $h) {
            $col = $cols[$i];
            $sheet->setCellValue("{$col}{$row}", $h);
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$col}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
            $sheet->getStyle("{$col}{$row}")->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Table Rows
        foreach ($violations as $idx => $v) {
            /** @var array<string, mixed> $v */
            $r = $row + 1 + intval($idx);
            $ruleId = is_string($v['ruleId'] ?? null) ? $v['ruleId'] : '';
            $standard = is_string($v['standard'] ?? null) ? $v['standard'] : 'WCAG 2.1';
            $severity = is_string($v['severity'] ?? null) ? $v['severity'] : 'WARNING';
            $desc = is_string($v['description'] ?? null) ? $v['description'] : '';
            $snippet = is_string($v['htmlSnippet'] ?? null) ? $v['htmlSnippet'] : '';

            $sheet->setCellValue("A{$r}", intval($idx) + 1);
            $sheet->setCellValue("B{$r}", $ruleId);
            $sheet->setCellValue("C{$r}", $standard);
            $sheet->setCellValue("D{$r}", $severity);
            $sheet->setCellValue("E{$r}", $desc);
            $sheet->setCellValue("F{$r}", $snippet);
        }

        // Auto-fit columns
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return strval(ob_get_clean());
    }
}
