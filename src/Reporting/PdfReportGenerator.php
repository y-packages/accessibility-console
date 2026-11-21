<?php

namespace YakNet\AccessibilityConsole\Reporting;

use Dompdf\Dompdf;
use Dompdf\Options;
use YakNet\AccessibilityConsole\Analytics\ComplianceMatrixGenerator;
use YakNet\AccessibilityConsole\Core\Violation;

class PdfReportGenerator
{
    /**
     * Generate a detailed PDF Accessibility Audit & Conformance Report.
     *
     * @param array<string, mixed> $scanResult Scan result metrics, compliance matrix, and violations array
     * @param string $targetUrl Target URL or file name audited
     * @return string Raw PDF binary data
     */
    public static function generatePdf(array $scanResult, string $targetUrl): string
    {
        if (!class_exists(Dompdf::class)) {
            throw new \RuntimeException('To generate PDF certificates, please install dompdf via: composer require dompdf/dompdf');
        }

        date_default_timezone_set('Europe/Istanbul');

        /** @var int $score */
        $score = is_numeric($scanResult['score'] ?? null) ? intval($scanResult['score']) : 0;
        
        /** @var array<int, array<string, mixed>> $violations */
        $violations = is_array($scanResult['violations'] ?? null) ? $scanResult['violations'] : [];
        $violationsCount = count($violations);
        $dateStr = date('d.m.Y H:i:s');
        $reportId = 'YAK-A11Y-' . date('Ymd') . '-' . strtoupper(substr(md5($targetUrl . date('Ymd')), 0, 6));

        // Calculate severity counts
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

        // Compliance Matrix (Principles and Levels)
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

        $scoreColor = $score >= 80 ? '#059669' : ($score >= 50 ? '#d97706' : '#dc2626');
        $scoreBg = $score >= 80 ? '#ecfdf5' : ($score >= 50 ? '#fffbeb' : '#fef2f2');
        $statusText = $score >= 80 ? 'WCAG 2.1 AA UYUMLU (PASS)' : ($score >= 50 ? 'KISMİ UYUMLU - İYİLEŞTİRME GEREKLİ' : 'UYUMSUZ - KRİTİK İHLALLER MEVCUT');

        $rawPrinciples = (isset($matrix['principles']) && is_array($matrix['principles']))
            ? $matrix['principles']
            : [
                'Perceivable' => ['violations' => 0, 'status' => 'Pass'],
                'Operable' => ['violations' => 0, 'status' => 'Pass'],
                'Understandable' => ['violations' => 0, 'status' => 'Pass'],
                'Robust' => ['violations' => 0, 'status' => 'Pass'],
            ];

        $principleTrNames = [
            'Perceivable' => '1. Algılanabilir (Perceivable)',
            'Operable' => '2. Çalıştırılabilir (Operable)',
            'Understandable' => '3. Anlaşılabilir (Understandable)',
            'Robust' => '4. Sağlam (Robust)'
        ];

        // Build Principles Table Rows
        $principlesRowsHtml = '';
        foreach ($rawPrinciples as $pKey => $pData) {
            $pKeyStr = (string)$pKey;
            $name = $principleTrNames[$pKeyStr] ?? $pKeyStr;
            $vCount = (is_array($pData) && isset($pData['violations']) && is_numeric($pData['violations'])) ? (int)$pData['violations'] : 0;
            $statusBadge = $vCount === 0 
                ? "<span style='color:#059669; font-weight:bold;'>✓ Başarılı</span>" 
                : "<span style='color:#dc2626; font-weight:bold;'>⚠ {$vCount} İhlal</span>";
            $principlesRowsHtml .= "
            <tr>
                <td style='padding:7px 10px; border-bottom:1px solid #e2e8f0; font-weight:600;'>{$name}</td>
                <td style='padding:7px 10px; border-bottom:1px solid #e2e8f0; text-align:center;'>{$vCount}</td>
                <td style='padding:7px 10px; border-bottom:1px solid #e2e8f0; text-align:center;'>{$statusBadge}</td>
            </tr>";
        }

        // Build Violations Detailed Listing (All Violations)
        $violationsHtml = '';
        if ($violationsCount === 0) {
            $violationsHtml = "
            <div style='background:#ecfdf5; border:1px solid #a7f3d0; border-radius:6px; padding:16px; text-align:center; color:#065f46; margin-top:15px;'>
                <div style='font-size:16px; font-weight:bold;'>🎉 Tebrikler! Hiçbir Erişilebilirlik İhlali Tespit Edilmedi</div>
                <div style='font-size:11px; margin-top:4px;'>Bu web sayfası W3C WCAG 2.1 standartlarına tam uyumluluk göstermektedir.</div>
            </div>";
        } else {
            foreach ($violations as $idx => $v) {
                $num = $idx + 1;
                $rawRule = $v['ruleId'] ?? '';
                $ruleId = htmlspecialchars(is_scalar($rawRule) ? (string)$rawRule : '');
                $rawDesc = $v['description'] ?? '';
                $desc = htmlspecialchars(is_scalar($rawDesc) ? (string)$rawDesc : '');
                $rawSnippet = $v['htmlSnippet'] ?? '';
                $snippet = htmlspecialchars(is_scalar($rawSnippet) ? (string)$rawSnippet : '');
                $rawSug = $v['suggestion'] ?? 'İlgili erişilebilirlik kuralını W3C standartlarına uygun olarak düzeltin.';
                $suggestion = htmlspecialchars(is_scalar($rawSug) ? (string)$rawSug : '');
                $rawStd = $v['standard'] ?? 'WCAG 2.1 AA';
                $standard = htmlspecialchars(is_scalar($rawStd) ? (string)$rawStd : 'WCAG 2.1 AA');
                $rawSev = $v['severity'] ?? 'ERROR';
                $severity = strtoupper(is_scalar($rawSev) ? (string)$rawSev : 'ERROR');
                $rawLine = $v['line'] ?? 0;
                $line = is_numeric($rawLine) ? (int)$rawLine : 0;

                $sevBadgeColor = ($severity === 'ERROR' || $severity === 'CRITICAL') ? '#dc2626' : '#d97706';
                $sevBgColor = ($severity === 'ERROR' || $severity === 'CRITICAL') ? '#fef2f2' : '#fffbeb';
                $sevText = ($severity === 'ERROR' || $severity === 'CRITICAL') ? 'KRİTİK HATA' : 'UYARI';

                $violationsHtml .= "
                <div style='page-break-inside:avoid; margin-bottom:12px; border:1px solid #e2e8f0; border-left:4px solid {$sevBadgeColor}; border-radius:4px; background:#ffffff;'>
                    <div style='padding:8px 12px; background:{$sevBgColor}; border-bottom:1px solid #e2e8f0;'>
                        <span style='font-weight:bold; color:#0f172a; font-size:11px;'>#{$num} [{$ruleId}]</span>
                        <span style='float:right;'>
                            <span style='background:#0f172a; color:#ffffff; padding:2px 6px; border-radius:3px; font-size:9px; font-weight:bold;'>{$standard}</span>
                            <span style='background:{$sevBadgeColor}; color:#ffffff; padding:2px 6px; border-radius:3px; font-size:9px; font-weight:bold;'>{$sevText}</span>
                            " . ($line > 0 ? "<span style='color:#64748b; font-size:9px; margin-left:6px;'>Satır: {$line}</span>" : "") . "
                        </span>
                    </div>
                    <div style='padding:10px 12px;'>
                        <div style='color:#1e293b; font-size:11px; margin-bottom:6px;'><b>İhlal Açıklaması:</b> {$desc}</div>
                        " . (!empty($snippet) ? "
                        <div style='background:#f8fafc; border:1px solid #cbd5e1; border-radius:3px; padding:6px 8px; font-family:monospace; font-size:9.5px; color:#be123c; margin-bottom:6px; word-wrap:break-word;'>
                            {$snippet}
                        </div>" : "") . "
                        <div style='color:#0f766e; font-size:10.5px; background:#f0fdfa; padding:6px 8px; border-radius:3px;'>
                            <b>💡 Çözüm ve Düzeltme Önerisi:</b> {$suggestion}
                        </div>
                    </div>
                </div>";
            }
        }

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <title>YakNet Erişilebilirlik Denetim Raporu</title>
            <style>
                @page { margin: 16mm 14mm 16mm 14mm; }
                * { font-family: 'DejaVu Sans', sans-serif; box-sizing: border-box; }
                body { color: #0f172a; line-height: 1.45; font-size: 10.5px; margin: 0; padding: 0; }
                
                .header-table { width: 100%; border-collapse: collapse; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 14px; }
                .brand-title { font-size: 16px; font-weight: bold; color: #0f172a; letter-spacing: 0.5px; }
                .brand-sub { font-size: 9.5px; color: #475569; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }
                .report-meta { text-align: right; font-size: 9.5px; color: #475569; }

                .scorecard-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
                .score-box { background: {$scoreBg}; border: 1.5px solid {$scoreColor}; border-radius: 6px; padding: 12px; text-align: center; }
                .score-number { font-size: 34px; font-weight: bold; color: {$scoreColor}; line-height: 1; }
                .score-status-badge { display: inline-block; background: {$scoreColor}; color: #ffffff; padding: 4px 12px; border-radius: 12px; font-weight: bold; font-size: 10.5px; margin-top: 6px; }

                .info-table { width: 100%; border-collapse: collapse; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; }
                .info-table td { padding: 6px 10px; font-size: 10px; border-bottom: 1px solid #e2e8f0; }

                .section-header { font-size: 12px; font-weight: bold; color: #0f172a; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 4px; margin: 16px 0 10px 0; text-transform: uppercase; letter-spacing: 0.5px; }

                .matrix-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
                .matrix-table th { background: #1e293b; color: #ffffff; padding: 7px 10px; font-size: 10px; font-weight: bold; text-align: left; }

                .footer-table { width: 100%; border-collapse: collapse; border-top: 1px solid #cbd5e1; padding-top: 10px; margin-top: 20px; font-size: 8.5px; color: #64748b; }
                .signature-box { border: 1px dashed #94a3b8; background: #f8fafc; border-radius: 4px; padding: 10px; text-align: center; margin-top: 15px; }
            </style>
        </head>
        <body>
            <!-- Başlık -->
            <table class='header-table'>
                <tr>
                    <td style='vertical-align:middle;'>
                        <div class='brand-title'>♿ YAKNET</div>
                        <div class='brand-sub'>Open-Source Accessibility Console • yak.net.tr</div>
                    </td>
                    <td class='report-meta' style='vertical-align:middle;'>
                        <div><b>Rapor Ref:</b> {$reportId}</div>
                        <div><b>Tarih:</b> {$dateStr}</div>
                        <div><b>Standart:</b> W3C WCAG 2.1 / 2.2 (Level AA)</div>
                    </td>
                </tr>
            </table>

            <!-- Başlık -->
            <div style='text-align:center; margin-bottom:14px;'>
                <div style='font-size:15px; font-weight:bold; color:#0f172a; text-transform:uppercase;'>WEB ERİŞİLEBİLİRLİK DENETİM VE UYUMLULUK RAPORU</div>
                <div style='font-size:9.5px; color:#64748b; margin-top:2px;'>W3C Web İçeriği Erişilebilirlik Kılavuzları (WCAG 2.1 &amp; 2.2) Uyarınca Hazırlanmıştır</div>
            </div>

            <!-- Skor ve Denetim Bilgisi Kartları -->
            <table class='scorecard-table'>
                <tr>
                    <td style='width:38%; vertical-align:top; padding-right:10px;'>
                        <div class='score-box'>
                            <div style='font-size:9.5px; font-weight:bold; color:#64748b; text-transform:uppercase;'>Genel Sağlık Skoru</div>
                            <div class='score-number'>{$score}<span style='font-size:16px; color:#64748b; font-weight:normal;'>/100</span></div>
                            <div class='score-status-badge'>{$statusText}</div>
                        </div>
                    </td>
                    <td style='width:62%; vertical-align:top;'>
                        <table class='info-table'>
                            <tr>
                                <td style='width:35%; font-weight:bold; color:#475569;'>Hedef URL / Adres:</td>
                                <td style='font-weight:bold; color:#0f172a; word-break:break-all;'>" . htmlspecialchars($targetUrl) . "</td>
                            </tr>
                            <tr>
                                <td style='font-weight:bold; color:#475569;'>Denetim Zamanı:</td>
                                <td>{$dateStr}</td>
                            </tr>
                            <tr>
                                <td style='font-weight:bold; color:#475569;'>Toplam İhlal Sayısı:</td>
                                <td><b>{$violationsCount}</b> Adet (Kritik: <b style='color:#dc2626;'>{$criticalCount}</b>, Uyarı: <b style='color:#d97706;'>{$warningCount}</b>)</td>
                            </tr>
                            <tr>
                                <td style='font-weight:bold; color:#475569;'>Denetim Motoru:</td>
                                <td>YakNet Accessibility Console v4.0</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- WCAG 4 Temel Prensip Uyumluluk Karnesi -->
            <div class='section-header'>📊 WCAG 2.1 Dört Temel İlke ve Uyumluluk Karnesi</div>
            <table class='matrix-table'>
                <thead>
                    <tr>
                        <th style='width:60%;'>WCAG Temel İlkesi</th>
                        <th style='width:20%; text-align:center;'>İhlal Sayısı</th>
                        <th style='width:20%; text-align:center;'>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    {$principlesRowsHtml}
                </tbody>
            </table>

            <!-- Detaylı İhlal Listesi -->
            <div class='section-header'>📋 Tespit Edilen Erişilebilirlik Bulguları ve Çözüm Planı ({$violationsCount} Kayıt)</div>
            {$violationsHtml}

            <!-- Doğrulama ve Rapor Özeti Bölümü -->
            <div style='page-break-inside:avoid; margin-top:20px;'>
                <div class='signature-box'>
                    <div style='font-weight:bold; font-size:10.5px; color:#0f172a;'>YAKNET ACCESSIBILITY CONSOLE — OTOMATİK DENETİM DOĞRULAMASI</div>
                    <div style='font-size:9px; color:#475569; margin:4px 0 8px 0;'>
                        Bu rapor, yukarıda belirtilen hedef için W3C Web Content Accessibility Guidelines (WCAG 2.1 / 2.2),
                        WAI-ARIA 1.2 ve EN 301 549 kılavuzlarına dayalı açık kaynaklı otomatik denetim sonucunu yansıtmaktadır.
                    </div>
                    <table style='width:100%; margin-top:8px;'>
                        <tr>
                            <td style='text-align:left; font-size:9px; color:#64748b;'>
                                <b>Doğrulama Özeti (SHA-256):</b> " . hash('sha256', $reportId . $targetUrl) . "<br>
                                <b>Organizasyon:</b> YakNet (Open Source)
                            </td>
                            <td style='text-align:right; font-size:9px;'>
                                <div style='font-weight:bold; color:#0f172a;'>YakNet Accessibility Console</div>
                                <div style='color:#059669; font-weight:bold;'>[ Otomatik Doğrulama Özeti Oluşturuldu ]</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Alt Bilgi -->
            <table class='footer-table'>
                <tr>
                    <td style='text-align:left;'>
                        YakNet Accessibility Console • Açık Kaynak Erişilebilirlik Denetim Platformu • https://forum.yak.net.tr
                    </td>
                    <td style='text-align:right;'>
                        Belge No: {$reportId}
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return strval($dompdf->output());
    }
}
