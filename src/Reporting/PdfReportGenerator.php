<?php

namespace YakNet\AccessibilityConsole\Reporting;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfReportGenerator
{
    /**
     * Generate a PDF Accessibility Certificate & Audit Report.
     *
     * @param array<string, mixed> $scanResult Scan result metrics and violations array
     * @param string $targetUrl Target URL or file name audited
     * @return string Raw PDF binary data
     */
    public static function generatePdf(array $scanResult, string $targetUrl): string
    {
        if (!class_exists(Dompdf::class)) {
            throw new \RuntimeException('To generate PDF certificates, please install dompdf via: composer require dompdf/dompdf');
        }

        /** @var int $score */
        $score = is_numeric($scanResult['score'] ?? null) ? intval($scanResult['score']) : 0;
        
        /** @var array<int, array<string, mixed>> $violations */
        $violations = is_array($scanResult['violations'] ?? null) ? $scanResult['violations'] : [];
        $violationsCount = count($violations);
        $dateStr = date('d.m.Y H:i');

        $scoreColor = $score >= 80 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444');
        $statusText = $score >= 80 ? 'WCAG 2.1 AA Uyumlu (Pass)' : ($score >= 50 ? 'İyileştirme Gerekli (Warning)' : 'Kritik İhlaller (Fail)');

        $violationsHtml = '';
        $slicedViolations = array_slice($violations, 0, 25);
        foreach ($slicedViolations as $idx => $v) {
            /** @var array<string, mixed> $v */
            $num = $idx + 1;
            $ruleIdVal = is_string($v['ruleId'] ?? null) ? $v['ruleId'] : '';
            $descVal = is_string($v['description'] ?? null) ? $v['description'] : '';
            $snippetVal = is_string($v['htmlSnippet'] ?? null) ? $v['htmlSnippet'] : '';
            $suggestionVal = is_string($v['suggestion'] ?? null) ? $v['suggestion'] : '';

            $ruleId = htmlspecialchars($ruleIdVal);
            $desc = htmlspecialchars($descVal);
            $snippet = htmlspecialchars($snippetVal);
            $suggestion = htmlspecialchars($suggestionVal);

            $violationsHtml .= "
            <div style='margin-bottom:12px; padding:10px; border-left:4px solid #ef4444; background:#f8fafc;'>
                <div style='font-weight:bold; color:#0f172a; font-size:12px;'>#{$num} [{$ruleId}] {$desc}</div>
                <div style='font-family:monospace; font-size:10px; color:#e11d48; margin:4px 0; background:#f1f5f9; padding:4px;'>{$snippet}</div>
                <div style='font-size:11px; color:#475569;'><b>Öneri:</b> {$suggestion}</div>
            </div>";
        }

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>WCAG 2.1 Erişilebilirlik Sertifikası ve Raporu</title>
            <style>
                body { font-family: sans-serif; color: #1e293b; line-height: 1.5; font-size: 12px; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #38bdf8; padding-bottom: 15px; margin-bottom: 20px; }
                .title { font-size: 20px; font-weight: bold; color: #0f172a; }
                .subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }
                .badge { display: inline-block; padding: 6px 16px; border-radius: 12px; color: #fff; font-weight: bold; font-size: 14px; background: {$scoreColor}; }
                .grid { margin-bottom: 20px; }
                .card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; margin-bottom: 10px; }
                .footer { text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px; font-size: 10px; color: #94a3b8; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='title'>♿ YakNet Accessibility Console</div>
                <div class='subtitle'>WCAG 2.1 AA Erişilebilirlik Uyumluluk Raporu</div>
            </div>

            <div class='card' style='text-align:center;'>
                <div style='font-size:11px; color:#64748b; text-transform:uppercase;'>Erişilebilirlik Sağlık Skoru</div>
                <div style='font-size:32px; font-weight:bold; color:{$scoreColor}; margin:6px 0;'>{$score} / 100</div>
                <div class='badge'>{$statusText}</div>
            </div>

            <div class='card'>
                <div><b>Hedef URL / Dizin:</b> " . htmlspecialchars($targetUrl) . "</div>
                <div><b>Tarama Tarihi:</b> {$dateStr}</div>
                <div><b>Tespit Edilen İhlal Sayısı:</b> {$violationsCount} Hata</div>
                <div><b>Raporu Üreten Motor:</b> YakNet AI & Accessibility Engine v3.1</div>
            </div>

            <h3 style='color:#0f172a; border-bottom:1px solid #cbd5e1; padding-bottom:6px;'>📋 Erişilebilirlik İhlalleri ve Çözüm Detayları</h3>
            {$violationsHtml}

            <div class='footer'>
                © {$dateStr} YakNet Bilişim — Türkiye'nin Yerli & Milli Erişilebilirlik Platformu (yak.net.tr)
            </div>
        </body>
        </html>";

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return strval($dompdf->output());
    }
}
