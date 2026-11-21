<?php

namespace YakNet\AccessibilityConsole\Reporting;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use YakNet\AccessibilityConsole\Analytics\ComplianceMatrixGenerator;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Reference\WcagDatabase;

class ExcelReportExporter
{
    /**
     * Export WCAG audit results as a multi-tab Excel (.xlsx) workbook.
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

        date_default_timezone_set('Europe/Istanbul');

        /** @var int $score */
        $score = is_numeric($scanResult['score'] ?? null) ? intval($scanResult['score']) : 0;

        /** @var array<int, array<string, mixed>> $violations */
        $violations = is_array($scanResult['violations'] ?? null) ? $scanResult['violations'] : [];
        $violationsCount = count($violations);
        $dateStr = date('d.m.Y H:i:s');
        $reportId = 'YAK-A11Y-' . date('Ymd') . '-' . strtoupper(substr(md5($targetUrl . date('Ymd')), 0, 6));

        // Severity counters
        $criticalCount = 0;
        $warningCount = 0;
        $infoCount = 0;
        foreach ($violations as $v) {
            $rawSev = $v['severity'] ?? 'ERROR';
            $sev = strtoupper(is_scalar($rawSev) ? (string)$rawSev : 'ERROR');
            if ($sev === 'ERROR' || $sev === 'CRITICAL') {
                $criticalCount++;
            } elseif ($sev === 'WARNING' || $sev === 'WARN') {
                $warningCount++;
            } else {
                $infoCount++;
            }
        }

        // Compliance Matrix
        $rawMatrix = $scanResult['complianceMatrix'] ?? null;
        /** @var array<string, mixed>|null $matrix */
        $matrix = is_array($rawMatrix) ? $rawMatrix : null;
        if (!$matrix) {
            $gen = new ComplianceMatrixGenerator();
            $pseudoViolations = [];
            foreach ($violations as $v) {
                $rawStd = $v['standard'] ?? 'WCAG 2.1 AA';
                $stdStr = is_scalar($rawStd) ? (string)$rawStd : 'WCAG 2.1 AA';
                $std = match($stdStr) {
                    'A' => \YakNet\AccessibilityConsole\Core\WCAGStandard::A,
                    'AAA' => \YakNet\AccessibilityConsole\Core\WCAGStandard::AAA,
                    default => \YakNet\AccessibilityConsole\Core\WCAGStandard::AA,
                };
                $rawRule = $v['ruleId'] ?? '';
                $ruleId = is_scalar($rawRule) ? (string)$rawRule : '';
                $rawDesc = $v['description'] ?? '';
                $desc = is_scalar($rawDesc) ? (string)$rawDesc : '';
                $rawLine = $v['line'] ?? 0;
                $line = is_numeric($rawLine) ? (int)$rawLine : 0;
                $rawSnippet = $v['htmlSnippet'] ?? '';
                $snippet = is_scalar($rawSnippet) ? (string)$rawSnippet : '';

                $pseudoViolations[] = new Violation(
                    ruleId: $ruleId,
                    message: $desc,
                    severity: \YakNet\AccessibilityConsole\Core\Severity::ERROR,
                    standard: $std,
                    location: ['line' => $line],
                    htmlSnippet: $snippet
                );
            }
            $matrix = $gen->generate($pseudoViolations, max(1, count($violations)));
        }

        $rawPrinciples = (isset($matrix['principles']) && is_array($matrix['principles']))
            ? $matrix['principles']
            : [
                'Perceivable' => ['violations' => 0, 'status' => 'Pass'],
                'Operable' => ['violations' => 0, 'status' => 'Pass'],
                'Understandable' => ['violations' => 0, 'status' => 'Pass'],
                'Robust' => ['violations' => 0, 'status' => 'Pass'],
            ];

        $statusText = $score >= 80 ? 'WCAG 2.1 AA Uyumlu (Pass)' : ($score >= 50 ? 'İyileştirme Gerekli (Warning)' : 'Kritik İhlaller (Fail)');
        $scoreHexColor = $score >= 80 ? '059669' : ($score >= 50 ? 'D97706' : 'DC2626');

        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // SHEET 1: ÖZET VE DENETİM KARNESİ (Executive Summary)
        // -------------------------------------------------------------
        $sheetSummary = $spreadsheet->getActiveSheet();
        $sheetSummary->setTitle('Denetim Özeti & Karne');
        $sheetSummary->setShowGridLines(true);

        // Header Banner
        $sheetSummary->setCellValue('A1', '♿ YAKNET ACCESSIBILITY CONSOLE — ERİŞİLEBİLİRLİK DENETİM RAPORU');
        $sheetSummary->mergeCells('A1:F1');
        $sheetSummary->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheetSummary->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheetSummary->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheetSummary->getRowDimension(1)->setRowHeight(32);

        $sheetSummary->setCellValue('A2', 'WCAG 2.1 / 2.2 Uyumluluk ve Erişilebilirlik Karnesi (YakNet Open Source)');
        $sheetSummary->mergeCells('A2:F2');
        $sheetSummary->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('64748B');
        $sheetSummary->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetSummary->getRowDimension(2)->setRowHeight(20);

        // Metadata Table
        $sheetSummary->setCellValue('A4', 'Rapor Bilgileri');
        $sheetSummary->mergeCells('A4:B4');
        $sheetSummary->getStyle('A4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheetSummary->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');

        $metaRows = [
            ['Rapor Referans No', $reportId],
            ['Denetlenen Web Adresi', $targetUrl],
            ['Denetim Zamanı', $dateStr],
            ['Denetim Standardı', 'W3C WCAG 2.1 / 2.2 (Level AA) & EN 301 549'],
            ['Denetim Motoru', 'YakNet Accessibility Console v4.0'],
        ];

        $r = 5;
        foreach ($metaRows as $m) {
            $sheetSummary->setCellValue("A{$r}", $m[0]);
            $sheetSummary->setCellValue("B{$r}", $m[1]);
            $sheetSummary->getStyle("A{$r}")->getFont()->setBold(true)->getColor()->setRGB('334155');
            $sheetSummary->getStyle("A{$r}:B{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $r++;
        }

        // Score KPI Card (Columns D:F, Rows 4:7)
        $sheetSummary->setCellValue('D4', 'Genel Sağlık Skoru');
        $sheetSummary->mergeCells('D4:F4');
        $sheetSummary->getStyle('D4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheetSummary->getStyle('D4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheetSummary->getStyle('D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheetSummary->setCellValue('D5', $score . ' / 100');
        $sheetSummary->mergeCells('D5:F6');
        $sheetSummary->getStyle('D5')->getFont()->setBold(true)->setSize(24)->getColor()->setRGB($scoreHexColor);
        $sheetSummary->getStyle('D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheetSummary->getStyle('D5:F6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

        $sheetSummary->setCellValue('D7', $statusText);
        $sheetSummary->mergeCells('D7:F7');
        $sheetSummary->getStyle('D7')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheetSummary->getStyle('D7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($scoreHexColor);
        $sheetSummary->getStyle('D7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetSummary->getStyle('D4:F7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($scoreHexColor);

        // Findings Statistics Table (Row 11)
        $sheetSummary->setCellValue('A11', 'İhlal Dağılım İstatistikleri');
        $sheetSummary->mergeCells('A11:C11');
        $sheetSummary->getStyle('A11')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheetSummary->getStyle('A11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');

        $statRows = [
            ['Toplam İhlal Sayısı', $violationsCount, '0F172A'],
            ['Kritik Hatalar (Error)', $criticalCount, 'DC2626'],
            ['Uyarılar (Warning)', $warningCount, 'D97706'],
            ['Bilgilendirmeler (Info)', $infoCount, '2563EB'],
        ];

        $r = 12;
        foreach ($statRows as $st) {
            $sheetSummary->setCellValue("A{$r}", $st[0]);
            $sheetSummary->mergeCells("A{$r}:B{$r}");
            $sheetSummary->setCellValue("C{$r}", $st[1]);
            $sheetSummary->getStyle("A{$r}")->getFont()->setBold(true);
            $sheetSummary->getStyle("C{$r}")->getFont()->setBold(true)->getColor()->setRGB($st[2]);
            $sheetSummary->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetSummary->getStyle("A{$r}:C{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $r++;
        }

        // WCAG 4 Principles Table (Row 11, Cols D:F)
        $sheetSummary->setCellValue('D11', 'WCAG 2.1 Dört Temel İlke Dağılımı');
        $sheetSummary->mergeCells('D11:F11');
        $sheetSummary->getStyle('D11')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheetSummary->getStyle('D11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');

        $principleTr = [
            'Perceivable' => '1. Algılanabilir (Perceivable)',
            'Operable' => '2. Çalıştırılabilir (Operable)',
            'Understandable' => '3. Anlaşılabilir (Understandable)',
            'Robust' => '4. Sağlam (Robust)'
        ];

        $r = 12;
        foreach ($rawPrinciples as $pKey => $pData) {
            $pKeyStr = (string)$pKey;
            $pName = $principleTr[$pKeyStr] ?? $pKeyStr;
            $vNum = (is_array($pData) && isset($pData['violations']) && is_numeric($pData['violations'])) ? (int)$pData['violations'] : 0;
            $sheetSummary->setCellValue("D{$r}", $pName);
            $sheetSummary->setCellValue("E{$r}", $vNum . ' İhlal');
            $sheetSummary->setCellValue("F{$r}", $vNum === 0 ? '✓ Geçti' : '⚠ İncele');
            $sheetSummary->getStyle("F{$r}")->getFont()->setBold(true)->getColor()->setRGB($vNum === 0 ? '059669' : 'DC2626');
            $sheetSummary->getStyle("D{$r}:F{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $r++;
        }

        $sheetSummary->getColumnDimension('A')->setWidth(22);
        $sheetSummary->getColumnDimension('B')->setWidth(38);
        $sheetSummary->getColumnDimension('C')->setWidth(14);
        $sheetSummary->getColumnDimension('D')->setWidth(26);
        $sheetSummary->getColumnDimension('E')->setWidth(14);
        $sheetSummary->getColumnDimension('F')->setWidth(18);

        // -------------------------------------------------------------
        // SHEET 2: TÜM İHLAL DETAYLARI (Detailed Violations Table)
        // -------------------------------------------------------------
        $sheetViolations = $spreadsheet->createSheet();
        $sheetViolations->setTitle('İhlal Detayları');
        $sheetViolations->setShowGridLines(true);

        $vHeaders = ['#', 'Kural ID', 'Standart', 'Ciddiyet', 'Satır', 'İhlal Açıklaması', 'Hatalı Kod Snippet', 'Çözüm ve Düzeltme Önerisi'];
        $vCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        // Table Header
        $vRow = 1;
        foreach ($vHeaders as $i => $h) {
            $col = $vCols[$i];
            $sheetViolations->setCellValue("{$col}{$vRow}", $h);
            $sheetViolations->getStyle("{$col}{$vRow}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheetViolations->getStyle("{$col}{$vRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
            $sheetViolations->getStyle("{$col}{$vRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheetViolations->getRowDimension($vRow)->setRowHeight(28);

        // Populate Table Rows
        $vRow = 2;
        foreach ($violations as $idx => $v) {
            $rawRule = $v['ruleId'] ?? '';
            $ruleId = is_scalar($rawRule) ? (string)$rawRule : '';
            $rawStd = $v['standard'] ?? 'WCAG 2.1 AA';
            $standard = is_scalar($rawStd) ? (string)$rawStd : 'WCAG 2.1 AA';
            $rawSev = $v['severity'] ?? 'ERROR';
            $severity = strtoupper(is_scalar($rawSev) ? (string)$rawSev : 'ERROR');
            $rawLine = $v['line'] ?? 0;
            $line = is_numeric($rawLine) ? (int)$rawLine : 0;
            $rawDesc = $v['description'] ?? '';
            $desc = is_scalar($rawDesc) ? (string)$rawDesc : '';
            $rawSnippet = $v['htmlSnippet'] ?? '';
            $snippet = is_scalar($rawSnippet) ? (string)$rawSnippet : '';
            $rawSug = $v['suggestion'] ?? '';
            $suggestion = is_scalar($rawSug) ? (string)$rawSug : '';

            $isCrit = ($severity === 'ERROR' || $severity === 'CRITICAL');
            $sevLabel = $isCrit ? 'KRİTİK' : 'UYARI';
            $sevColor = $isCrit ? 'DC2626' : 'D97706';
            $rowBg = ($idx % 2 === 1) ? 'F8FAFC' : 'FFFFFF';

            $sheetViolations->setCellValue("A{$vRow}", $idx + 1);
            $sheetViolations->setCellValue("B{$vRow}", $ruleId);
            $sheetViolations->setCellValue("C{$vRow}", $standard);
            $sheetViolations->setCellValue("D{$vRow}", $sevLabel);
            $sheetViolations->setCellValue("E{$vRow}", $line > 0 ? $line : '-');
            $sheetViolations->setCellValue("F{$vRow}", $desc);
            $sheetViolations->setCellValue("G{$vRow}", $snippet);
            $sheetViolations->setCellValue("H{$vRow}", $suggestion);

            // Row styling
            $sheetViolations->getStyle("A{$vRow}:H{$vRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rowBg);
            $sheetViolations->getStyle("A{$vRow}:H{$vRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

            $sheetViolations->getStyle("A{$vRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetViolations->getStyle("C{$vRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetViolations->getStyle("D{$vRow}")->getFont()->setBold(true)->getColor()->setRGB($sevColor);
            $sheetViolations->getStyle("D{$vRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetViolations->getStyle("E{$vRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Monospace font for code snippet
            $sheetViolations->getStyle("G{$vRow}")->getFont()->setName('Consolas')->setSize(9.5);

            $sheetViolations->getStyle("F{$vRow}")->getAlignment()->setWrapText(true);
            $sheetViolations->getStyle("G{$vRow}")->getAlignment()->setWrapText(true);
            $sheetViolations->getStyle("H{$vRow}")->getAlignment()->setWrapText(true);

            $sheetViolations->getRowDimension($vRow)->setRowHeight(36);
            $vRow++;
        }

        // Enable auto-filter
        if ($vRow > 2) {
            $sheetViolations->setAutoFilter("A1:H" . ($vRow - 1));
        }

        $sheetViolations->getColumnDimension('A')->setWidth(6);
        $sheetViolations->getColumnDimension('B')->setWidth(26);
        $sheetViolations->getColumnDimension('C')->setWidth(14);
        $sheetViolations->getColumnDimension('D')->setWidth(12);
        $sheetViolations->getColumnDimension('E')->setWidth(8);
        $sheetViolations->getColumnDimension('F')->setWidth(40);
        $sheetViolations->getColumnDimension('G')->setWidth(35);
        $sheetViolations->getColumnDimension('H')->setWidth(40);

        // -------------------------------------------------------------
        // SHEET 3: STANDARTLAR VE KURALLAR KATALOĞU (Rules Reference)
        // -------------------------------------------------------------
        $sheetRules = $spreadsheet->createSheet();
        $sheetRules->setTitle('WCAG Kural Kataloğu');
        $sheetRules->setShowGridLines(true);

        WcagDatabase::load();
        $criteria = WcagDatabase::all();

        $sheetRules->setCellValue('A1', '#');
        $sheetRules->setCellValue('B1', 'Kural Kodu');
        $sheetRules->setCellValue('C1', 'WCAG Kriteri');
        $sheetRules->setCellValue('D1', 'Seviye');
        $sheetRules->setCellValue('E1', 'Açıklama');
        $sheetRules->setCellValue('F1', 'W3C Resmi Kılavuz Bağlantısı');

        $sheetRules->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheetRules->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheetRules->getRowDimension(1)->setRowHeight(26);

        $rRow = 2;
        $idx = 1;
        foreach ($criteria as $code => $cr) {
            $sheetRules->setCellValue("A{$rRow}", $idx);
            $sheetRules->setCellValue("B{$rRow}", $cr->id);
            $sheetRules->setCellValue("C{$rRow}", $cr->title);
            $sheetRules->setCellValue("D{$rRow}", $cr->level);
            $sheetRules->setCellValue("E{$rRow}", $cr->description);
            $sheetRules->setCellValue("F{$rRow}", $cr->url);

            $sheetRules->getStyle("A{$rRow}:F{$rRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheetRules->getStyle("D{$rRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetRules->getStyle("F{$rRow}")->getFont()->getColor()->setRGB('2563EB');
            $sheetRules->getStyle("F{$rRow}")->getFont()->setUnderline(true);
            $rRow++;
            $idx++;
        }

        $sheetRules->getColumnDimension('A')->setWidth(6);
        $sheetRules->getColumnDimension('B')->setWidth(26);
        $sheetRules->getColumnDimension('C')->setWidth(24);
        $sheetRules->getColumnDimension('D')->setWidth(10);
        $sheetRules->getColumnDimension('E')->setWidth(50);
        $sheetRules->getColumnDimension('F')->setWidth(40);

        // Set active sheet back to Summary
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return strval(ob_get_clean());
    }
}
