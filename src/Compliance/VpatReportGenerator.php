<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Compliance;

use YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

/**
 * Generates VPAT 2.5 (Voluntary Product Accessibility Template) / ACR
 * (Accessibility Conformance Report) documents aligned with WCAG 2.1, WCAG 2.2,
 * Revised Section 508, and EN 301 549.
 */
class VpatReportGenerator
{
    /**
     * Master catalog of WCAG 2.1 & 2.2 Success Criteria (Code => [Title, Level, Version, Automated]).
     *
     * @var array<string, array{title: string, level: string, version: string, automated: bool}>
     */
    private const CRITERIA_CATALOG = [
        '1.1.1' => ['title' => 'Non-text Content', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '1.2.1' => ['title' => 'Audio-only and Video-only (Prerecorded)', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '1.2.2' => ['title' => 'Captions (Prerecorded)', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '1.3.1' => ['title' => 'Info and Relationships', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '1.3.2' => ['title' => 'Meaningful Sequence', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '1.3.4' => ['title' => 'Orientation', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '1.3.5' => ['title' => 'Identify Input Purpose', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '1.4.1' => ['title' => 'Use of Color', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '1.4.2' => ['title' => 'Audio Control', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '1.4.3' => ['title' => 'Contrast (Minimum)', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '1.4.4' => ['title' => 'Resize Text', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '1.4.6' => ['title' => 'Contrast (Enhanced)', 'level' => 'AAA', 'version' => '2.1', 'automated' => true],
        '1.4.8' => ['title' => 'Visual Presentation', 'level' => 'AAA', 'version' => '2.1', 'automated' => true],
        '1.4.10' => ['title' => 'Reflow', 'level' => 'AA', 'version' => '2.1', 'automated' => false],
        '1.4.11' => ['title' => 'Non-text Contrast', 'level' => 'AA', 'version' => '2.1', 'automated' => false],
        '1.4.12' => ['title' => 'Text Spacing', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '1.4.13' => ['title' => 'Content on Hover or Focus', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '2.1.1' => ['title' => 'Keyboard', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.1.2' => ['title' => 'No Keyboard Trap', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.1.4' => ['title' => 'Character Key Shortcuts', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.2.1' => ['title' => 'Timing Adjustable', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.2.2' => ['title' => 'Pause, Stop, Hide', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.3.1' => ['title' => 'Three Flashes or Below Threshold', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.3.3' => ['title' => 'Animation from Interactions', 'level' => 'AAA', 'version' => '2.1', 'automated' => true],
        '2.4.1' => ['title' => 'Bypass Blocks', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.4.2' => ['title' => 'Page Titled', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.4.3' => ['title' => 'Focus Order', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.4.4' => ['title' => 'Link Purpose (In Context)', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.4.6' => ['title' => 'Headings and Labels', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '2.4.7' => ['title' => 'Focus Visible', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '2.4.8' => ['title' => 'Location', 'level' => 'AAA', 'version' => '2.1', 'automated' => true],
        '2.4.11' => ['title' => 'Focus Not Obscured (Minimum)', 'level' => 'AA', 'version' => '2.2', 'automated' => true],
        '2.4.13' => ['title' => 'Focus Appearance', 'level' => 'AAA', 'version' => '2.2', 'automated' => true],
        '2.5.2' => ['title' => 'Pointer Cancellation', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.5.3' => ['title' => 'Label in Name', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '2.5.7' => ['title' => 'Dragging Movements', 'level' => 'AA', 'version' => '2.2', 'automated' => true],
        '2.5.8' => ['title' => 'Target Size (Minimum)', 'level' => 'AA', 'version' => '2.2', 'automated' => true],
        '3.1.1' => ['title' => 'Language of Page', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '3.1.2' => ['title' => 'Language of Parts', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
        '3.1.4' => ['title' => 'Abbreviations', 'level' => 'AAA', 'version' => '2.1', 'automated' => true],
        '3.1.5' => ['title' => 'Reading Level', 'level' => 'AAA', 'version' => '2.1', 'automated' => true],
        '3.2.2' => ['title' => 'On Input', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '3.2.6' => ['title' => 'Consistent Help', 'level' => 'A', 'version' => '2.2', 'automated' => true],
        '3.3.1' => ['title' => 'Error Identification', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '3.3.2' => ['title' => 'Labels or Instructions', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '3.3.7' => ['title' => 'Redundant Entry', 'level' => 'A', 'version' => '2.2', 'automated' => true],
        '3.3.8' => ['title' => 'Accessible Authentication (Minimum)', 'level' => 'AA', 'version' => '2.2', 'automated' => true],
        '4.1.1' => ['title' => 'Parsing', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '4.1.2' => ['title' => 'Name, Role, Value', 'level' => 'A', 'version' => '2.1', 'automated' => true],
        '4.1.3' => ['title' => 'Status Messages', 'level' => 'AA', 'version' => '2.1', 'automated' => true],
    ];

    /**
     * Build structured VPAT 2.5 data model from violations.
     *
     * @param Violation[] $violations
     * @param string $productName
     * @param string $productVersion
     * @param string $evaluator
     * @return array{
     *     vpat_version: string,
     *     generated_at: string,
     *     product_name: string,
     *     product_version: string,
     *     evaluator: string,
     *     tool: string,
     *     health_score: int,
     *     total_violations: int,
     *     active_rules_count: int,
     *     summary: array{supports: int, partially_supports: int, does_not_support: int, not_evaluated: int},
     *     criteria: array<string, array{code: string, title: string, level: string, version: string, conformance: string, violations_count: int, remarks: string}>
     * }
     */
    public function buildReportData(
        array $violations,
        string $productName = 'Web Application',
        string $productVersion = '1.0.0',
        string $evaluator = 'YakNet Accessibility Console Automated Evaluation'
    ): array {
        /** @var array<string, Violation[]> $byCriterion */
        $byCriterion = [];

        foreach ($violations as $violation) {
            $code = $this->extractCriterionCode($violation->ruleId);
            $byCriterion[$code][] = $violation;
        }

        $summary = [
            'supports' => 0,
            'partially_supports' => 0,
            'does_not_support' => 0,
            'not_evaluated' => 0,
        ];

        $criteriaRows = [];
        foreach (self::CRITERIA_CATALOG as $code => $meta) {
            $matched = $byCriterion[$code] ?? [];
            $count = count($matched);

            if (!$meta['automated'] && $count === 0) {
                $conformance = 'Not Evaluated';
                $remarks = 'Requires manual human/assistive-technology verification.';
                $summary['not_evaluated']++;
            } elseif ($count === 0) {
                $conformance = 'Supports';
                $remarks = 'No automated violations detected during static & DOM evaluation.';
                $summary['supports']++;
            } else {
                $hasSevere = false;
                $ruleIds = [];
                foreach ($matched as $v) {
                    $ruleIds[$v->ruleId] = true;
                    if ($v->severity === Severity::CRITICAL || $v->severity === Severity::ERROR) {
                        $hasSevere = true;
                    }
                }
                if ($hasSevere) {
                    $conformance = 'Does Not Support';
                    $summary['does_not_support']++;
                } else {
                    $conformance = 'Partially Supports';
                    $summary['partially_supports']++;
                }
                $remarks = sprintf(
                    '%d violation(s) detected across rule(s): %s.',
                    $count,
                    implode(', ', array_keys($ruleIds))
                );
            }

            $criteriaRows[$code] = [
                'code' => $code,
                'title' => $meta['title'],
                'level' => $meta['level'],
                'version' => $meta['version'],
                'conformance' => $conformance,
                'violations_count' => $count,
                'remarks' => $remarks,
            ];
        }

        $metrics = AccessibilityScoreCalculator::calculate($violations);

        return [
            'vpat_version' => '2.5 (WCAG / Revised 508 / EN 301 549)',
            'generated_at' => date('Y-m-d\TH:i:sP'),
            'product_name' => $productName,
            'product_version' => $productVersion,
            'evaluator' => $evaluator,
            'tool' => 'YakNet Accessibility Console v4.0.0',
            'health_score' => $metrics['score'],
            'total_violations' => count($violations),
            'active_rules_count' => count(RuleLevels::getRulesForLevel(9)),
            'summary' => $summary,
            'criteria' => $criteriaRows,
        ];
    }

    /**
     * Render VPAT 2.5 Accessibility Conformance Report in Markdown format.
     *
     * @param Violation[] $violations
     */
    public function renderMarkdown(
        array $violations,
        string $productName = 'Web Application',
        string $productVersion = '1.0.0',
        string $evaluator = 'YakNet Accessibility Console Automated Evaluation'
    ): string {
        $data = $this->buildReportData($violations, $productName, $productVersion, $evaluator);

        $md = [];
        $md[] = '# Accessibility Conformance Report (VPAT 2.5)';
        $md[] = '';
        $md[] = '**WCAG 2.1 / 2.2, Revised Section 508 & EN 301 549 Edition**';
        $md[] = '';
        $md[] = '| Field | Value |';
        $md[] = '| :--- | :--- |';
        $md[] = '| **Product Name** | ' . $data['product_name'] . ' |';
        $md[] = '| **Product Version** | ' . $data['product_version'] . ' |';
        $md[] = '| **Report Date** | ' . $data['generated_at'] . ' |';
        $md[] = '| **Evaluator** | ' . $data['evaluator'] . ' |';
        $md[] = '| **Evaluation Tool** | ' . $data['tool'] . ' |';
        $md[] = '| **Health Score** | ' . $data['health_score'] . '/100 |';
        $md[] = '| **Total Automated Violations** | ' . $data['total_violations'] . ' |';
        $md[] = '';
        $md[] = '## Executive Conformance Summary';
        $md[] = '';
        $md[] = sprintf(
            '- **Supports:** %d criteria | **Partially Supports:** %d | **Does Not Support:** %d | **Not Evaluated (Manual):** %d',
            $data['summary']['supports'],
            $data['summary']['partially_supports'],
            $data['summary']['does_not_support'],
            $data['summary']['not_evaluated']
        );
        $md[] = '';

        foreach (['A', 'AA', 'AAA'] as $level) {
            $md[] = "## Table: WCAG 2.1 / 2.2 Level {$level}";
            $md[] = '';
            $md[] = '| Criteria | WCAG | Conformance Level | Remarks and Explanations |';
            $md[] = '| :--- | :---: | :--- | :--- |';
            foreach ($data['criteria'] as $row) {
                if ($row['level'] !== $level) {
                    continue;
                }
                $md[] = sprintf(
                    '| %s %s | %s | **%s** | %s |',
                    $row['code'],
                    $row['title'],
                    $row['version'],
                    $row['conformance'],
                    $row['remarks']
                );
            }
            $md[] = '';
        }

        $md[] = '---';
        $md[] = '_Generated by [YakNet Accessibility Console](https://forum.yak.net.tr) (Open Source, MIT License). Automated results should be paired with manual screen-reader and keyboard testing for complete legal attestation._';
        $md[] = '';

        return implode("\n", $md);
    }

    /**
     * Render VPAT 2.5 Accessibility Conformance Report in standalone HTML format.
     *
     * @param Violation[] $violations
     */
    public function renderHtml(
        array $violations,
        string $productName = 'Web Application',
        string $productVersion = '1.0.0',
        string $evaluator = 'YakNet Accessibility Console Automated Evaluation'
    ): string {
        $data = $this->buildReportData($violations, $productName, $productVersion, $evaluator);

        $tablesHtml = '';
        foreach (['A', 'AA', 'AAA'] as $level) {
            $rowsHtml = '';
            foreach ($data['criteria'] as $row) {
                if ($row['level'] !== $level) {
                    continue;
                }
                $badgeColor = match ($row['conformance']) {
                    'Supports' => '#059669',
                    'Partially Supports' => '#d97706',
                    'Does Not Support' => '#dc2626',
                    default => '#475569',
                };
                $rowsHtml .= sprintf(
                    '<tr><td><strong>%s</strong> %s <span class="ver">WCAG %s</span></td><td><span class="badge" style="background:%s">%s</span></td><td>%s</td></tr>',
                    htmlspecialchars($row['code']),
                    htmlspecialchars($row['title']),
                    htmlspecialchars($row['version']),
                    $badgeColor,
                    htmlspecialchars($row['conformance']),
                    htmlspecialchars($row['remarks'])
                );
            }

            $tablesHtml .= "<h2>WCAG 2.1 / 2.2 Level {$level}</h2>"
                . '<table><thead><tr><th scope="col">Success Criterion</th><th scope="col">Conformance Level</th><th scope="col">Remarks and Explanations</th></tr></thead><tbody>'
                . $rowsHtml
                . '</tbody></table>';
        }

        $productEsc = htmlspecialchars($data['product_name']);
        $versionEsc = htmlspecialchars($data['product_version']);
        $evalEsc = htmlspecialchars($data['evaluator']);
        $dateEsc = htmlspecialchars($data['generated_at']);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VPAT 2.5 Accessibility Conformance Report — {$productEsc}</title>
<style>
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color: #0f172a; background: #f8fafc; margin: 0; padding: 2rem; line-height: 1.5; }
  main { max-width: 1080px; margin: 0 auto; background: #ffffff; padding: 2rem 2.5rem; border-radius: 10px; box-shadow: 0 2px 12px rgba(15, 23, 42, 0.08); }
  h1 { margin-top: 0; color: #0f172a; }
  h2 { margin-top: 2rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.4rem; }
  .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
  .card { background: #f1f5f9; padding: 1rem; border-radius: 8px; border-left: 4px solid #2563eb; }
  .card strong { display: block; font-size: 0.8rem; text-transform: uppercase; color: #475569; }
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.92rem; }
  th, td { text-align: left; padding: 0.65rem 0.8rem; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
  th { background: #1e293b; color: #ffffff; }
  .badge { display: inline-block; color: #ffffff; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; white-space: nowrap; }
  .ver { font-size: 0.75rem; color: #64748b; margin-left: 0.4rem; }
  footer { margin-top: 2.5rem; font-size: 0.85rem; color: #475569; border-top: 1px solid #e2e8f0; padding-top: 1rem; }
</style>
</head>
<body>
<main>
  <h1>VPAT 2.5 Accessibility Conformance Report</h1>
  <p>WCAG 2.1 / 2.2, Revised Section 508 &amp; EN 301 549 Evaluation</p>
  <div class="meta-grid">
    <div class="card"><strong>Product</strong>{$productEsc} ({$versionEsc})</div>
    <div class="card"><strong>Health Score</strong>{$data['health_score']} / 100</div>
    <div class="card"><strong>Evaluator</strong>{$evalEsc}</div>
    <div class="card"><strong>Date</strong>{$dateEsc}</div>
  </div>
  <p>
    <strong>Supports:</strong> {$data['summary']['supports']} &nbsp;|&nbsp;
    <strong>Partially Supports:</strong> {$data['summary']['partially_supports']} &nbsp;|&nbsp;
    <strong>Does Not Support:</strong> {$data['summary']['does_not_support']} &nbsp;|&nbsp;
    <strong>Not Evaluated (Manual):</strong> {$data['summary']['not_evaluated']}
  </p>
  {$tablesHtml}
  <footer>
    Generated by <strong>YakNet Accessibility Console v4.0.0</strong> (Open Source, MIT License).
  </footer>
</main>
</body>
</html>
HTML;
    }

    /**
     * Render VPAT 2.5 Accessibility Conformance Report in JSON format.
     *
     * @param Violation[] $violations
     */
    public function renderJson(
        array $violations,
        string $productName = 'Web Application',
        string $productVersion = '1.0.0',
        string $evaluator = 'YakNet Accessibility Console Automated Evaluation'
    ): string {
        $data = $this->buildReportData($violations, $productName, $productVersion, $evaluator);
        return (string)json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function extractCriterionCode(string $ruleId): string
    {
        if (preg_match('/WCAG_(\d+)_(\d+)_(\d+)/', $ruleId, $m)) {
            return $m[1] . '.' . $m[2] . '.' . $m[3];
        }

        // Map legacy non-numeric rule IDs to their canonical WCAG Success Criteria
        return match ($ruleId) {
            'HtmlSyntaxValid', 'DuplicateId' => '4.1.1',
            'HeadingOrder', 'BannedSelectors' => '1.3.1',
            'AccesskeyDuplicate' => '2.1.1',
            'A11Y_VIEWPORT' => '1.4.4',
            'WCAG_LINK_TARGET_BLANK' => '3.2.2',
            'WCAG_FONT_TAG_BANNED' => '1.3.1',
            'AriaRole' => '4.1.2',
            'MetaCharsetPresent' => '4.1.1',
            default => '4.1.2',
        };
    }
}
