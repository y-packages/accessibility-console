<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;

class HtmlDashboardReporter
{
    /**
     * @param array<string, Violation[]> $results
     */
    public function render(array $results, string $target): string
    {
        $totalPages = count($results);
        $date = date('Y-m-d H:i:s');

        // Calculate severity, standard counts and overall average score
        $criticalCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        $infoCount = 0;

        $standardA = 0;
        $standardAA = 0;
        $standardAAA = 0;

        $pageScores = [];
        $totalViolations = 0;

        foreach ($results as $url => $violations) {
            $totalViolations += count($violations);
            $pageCrit = 0;
            $pageErr = 0;
            $pageWarn = 0;
            $pageInfo = 0;

            foreach ($violations as $v) {
                switch ($v->severity) {
                    case \YakNet\AccessibilityConsole\Core\Severity::CRITICAL:
                        $criticalCount++;
                        $pageCrit++;
                        break;
                    case \YakNet\AccessibilityConsole\Core\Severity::ERROR:
                        $errorCount++;
                        $pageErr++;
                        break;
                    case \YakNet\AccessibilityConsole\Core\Severity::WARNING:
                        $warningCount++;
                        $pageWarn++;
                        break;
                    case \YakNet\AccessibilityConsole\Core\Severity::INFO:
                        $infoCount++;
                        $pageInfo++;
                        break;
                }

                switch ($v->standard) {
                    case \YakNet\AccessibilityConsole\Core\WCAGStandard::A:
                        $standardA++;
                        break;
                    case \YakNet\AccessibilityConsole\Core\WCAGStandard::AA:
                        $standardAA++;
                        break;
                    case \YakNet\AccessibilityConsole\Core\WCAGStandard::AAA:
                        $standardAAA++;
                        break;
                }
            }

            $deductions = ($pageCrit * 15) + ($pageErr * 10) + ($pageWarn * 4) + ($pageInfo * 1);
            $pageScores[$url] = max(0, min(100, 100 - $deductions));
        }

        $averageScore = $totalPages > 0 ? (int)round(array_sum($pageScores) / $totalPages) : 100;

        // Generate Page Selector options
        $pageOptions = "<option value=\"all\">Tüm Sayfalar ({$totalPages} sayfa, {$totalViolations} ihlal)</option>";
        foreach ($results as $url => $violations) {
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $vCount = count($violations);
            $pageOptions .= "<option value=\"{$escapedUrl}\">{$escapedUrl} ({$vCount} ihlal)</option>";
        }

        $cards = '';
        $cardIndex = 0;
        foreach ($results as $url => $violations) {
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            foreach ($violations as $v) {
                $severityClass = strtolower($v->severity->value);
                $standardVal = $v->standard->value;
                $aiSuggestion = $v->fixSuggestion;
                $explanation = '';
                $fixedCode = '';

                if ($aiSuggestion && str_contains($aiSuggestion, 'FIX:')) {
                    preg_match('/EXPLANATION:(.*)FIX:(.*)/s', $aiSuggestion, $matches);
                    $explanation = trim($matches[1] ?? '');
                    $fixedCode = trim($matches[2] ?? '');
                    $fixedCode = preg_replace('/^```html\s*|\s*```$/i', '', $fixedCode);
                } else {
                    $fixedCode = $aiSuggestion;
                }

                $escapedSnippet = htmlspecialchars($v->htmlSnippet, ENT_QUOTES, 'UTF-8');
                $escapedFixed = $fixedCode ? htmlspecialchars($fixedCode, ENT_QUOTES, 'UTF-8') : '';
                $escapedExplanation = $explanation ? htmlspecialchars($explanation, ENT_QUOTES, 'UTF-8') : '';
                $locationStr = $v->location ? htmlspecialchars($v->location['file'] . ':' . $v->location['line'], ENT_QUOTES, 'UTF-8') : 'N/A';
                $escapedRuleId = htmlspecialchars($v->ruleId, ENT_QUOTES, 'UTF-8');
                $escapedMessage = htmlspecialchars($v->message, ENT_QUOTES, 'UTF-8');

                $hasAiFix = !empty($fixedCode);
                $aiTabButton = $hasAiFix ? "<button class=\"tab-btn\" id=\"tab-ai-{$cardIndex}\" role=\"tab\" aria-selected=\"false\" aria-controls=\"panel-ai-{$cardIndex}\" tabindex=\"-1\">Yapay Zeka Önerisi</button>" : "";
                $aiTabPanel = $hasAiFix ? "
                <div class=\"tab-panel\" id=\"panel-ai-{$cardIndex}\" role=\"tabpanel\" aria-labelledby=\"tab-ai-{$cardIndex}\" hidden>
                    <div class=\"ai-recommendation\">
                        " . ($escapedExplanation ? "<div class=\"ai-explanation\"><strong>Öneri Açıklaması:</strong> {$escapedExplanation}</div>" : "") . "
                        <div class=\"code-wrapper\">
                            <pre><code id=\"code-ai-{$cardIndex}\">{$escapedFixed}</code></pre>
                            <button class=\"copy-btn\" aria-label=\"{$escapedRuleId} kuralı için önerilen düzeltmeyi kopyala\" onclick=\"copyCode('#code-ai-{$cardIndex}', this)\">
                                <svg class=\"icon-copy\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><rect x=\"9\" y=\"9\" width=\"13\" height=\"13\" rx=\"2\" ry=\"2\"></rect><path d=\"M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1\"></path></svg>
                                <span>Kopyala</span>
                            </button>
                        </div>
                    </div>
                </div>" : "";

                $cards .= "
                <div class=\"violation-card\" data-severity=\"{$severityClass}\" data-standard=\"{$standardVal}\" data-rule=\"{$escapedRuleId}\" data-message=\"{$escapedMessage}\" data-page=\"{$escapedUrl}\">
                    <div class=\"card-header\">
                        <div class=\"badge-row\">
                            <span class=\"badge rule-badge\">{$escapedRuleId}</span>
                            <span class=\"badge standard-badge\">WCAG {$standardVal}</span>
                            <span class=\"badge severity-badge severity-{$severityClass}\">
                                <span class=\"dot\"></span>
                                " . ucfirst($severityClass) . "
                            </span>
                        </div>
                        <div class=\"location-row\">
                            <svg class=\"icon-location\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z\"></path><circle cx=\"12\" cy=\"10\" r=\"3\"></circle></svg>
                            <span>{$locationStr}</span>
                        </div>
                    </div>
                    <div class=\"card-body\">
                        <h3 class=\"violation-msg\">{$escapedMessage}</h3>
                        
                        <div class=\"code-viewer\">
                            <div class=\"tab-list\" role=\"tablist\">
                                <button class=\"tab-btn active\" id=\"tab-orig-{$cardIndex}\" role=\"tab\" aria-selected=\"true\" aria-controls=\"panel-orig-{$cardIndex}\">Mevcut Kod</button>
                                {$aiTabButton}
                            </div>
                            <div class=\"tab-panel active\" id=\"panel-orig-{$cardIndex}\" role=\"tabpanel\" aria-labelledby=\"tab-orig-{$cardIndex}\">
                                <pre><code>{$escapedSnippet}</code></pre>
                            </div>
                            {$aiTabPanel}
                        </div>

                        <div class=\"page-meta-row\" style=\"margin-top: 12px; font-size: 0.8rem; color: var(--text-secondary); display: flex; align-items: center; gap: 6px;\">
                            <svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"width:14px;height:14px;color:var(--text-muted);\"><path d=\"M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71\"></path><path d=\"M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71\"></path></svg>
                            <span>Kaynak Sayfa: <strong style=\"word-break: break-all;\">{$escapedUrl}</strong></span>
                        </div>
                    </div>
                </div>";

                $cardIndex++;
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="YakNet A11y Accessibility Audit Report">
    <title>YakNet Erişilebilirlik Raporu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-app: #0b0f19;
            --bg-card: rgba(17, 24, 39, 0.7);
            --border-card: rgba(255, 255, 255, 0.08);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            
            --color-primary: #6366f1;
            --color-primary-hover: #4f46e5;
            
            --color-critical: #f43f5e;
            --bg-critical: rgba(244, 63, 94, 0.15);
            
            --color-error: #f97316;
            --bg-error: rgba(249, 115, 22, 0.15);
            
            --color-warning: #eab308;
            --bg-warning: rgba(234, 179, 8, 0.15);
            
            --color-info: #3b82f6;
            --bg-info: rgba(59, 130, 246, 0.15);
            
            --color-success: #10b981;
            --bg-success: rgba(16, 185, 129, 0.15);
            
            --shadow-premium: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
            --backdrop-blur: blur(16px);
            --font-primary: 'Outfit', 'Inter', sans-serif;
            --font-secondary: 'Inter', sans-serif;
            --font-code: 'Courier New', Courier, monospace;
        }

        body.light-theme {
            --bg-app: #f3f4f6;
            --bg-card: rgba(255, 255, 255, 0.9);
            --border-card: rgba(0, 0, 0, 0.08);
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-muted: #9ca3af;
            
            --shadow-premium: 0 10px 30px -10px rgba(31, 38, 135, 0.08);
            
            --color-critical: #e11d48;
            --bg-critical: rgba(225, 29, 72, 0.1);
            
            --color-error: #ea580c;
            --bg-error: rgba(234, 88, 12, 0.1);
            
            --color-warning: #ca8a04;
            --bg-warning: rgba(202, 138, 4, 0.1);
            
            --color-info: #2563eb;
            --bg-info: rgba(37, 99, 235, 0.1);
            
            --color-success: #059669;
            --bg-success: rgba(5, 150, 105, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-secondary);
            background-color: var(--bg-app);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
            min-height: 100vh;
            padding: 24px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Glassmorphic Cards styling */
        .glass-panel {
            background: var(--bg-card);
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            box-shadow: var(--shadow-premium);
            padding: 24px;
            transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        /* Header styling */
        .app-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-container {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--color-primary), #a78bfa);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.4);
        }

        .logo-container svg {
            width: 24px;
            height: 24px;
        }

        .title-group h1 {
            font-family: var(--font-primary);
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .title-group p {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .header-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .meta-tag {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-card);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        body.light-theme .meta-tag {
            background: rgba(0, 0, 0, 0.03);
        }

        .theme-btn {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .theme-btn:hover, .theme-btn:focus-visible {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        .theme-btn svg {
            width: 20px;
            height: 20px;
        }

        /* Metrics grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .metric-card {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Circular gauge chart */
        .gauge-container {
            position: relative;
            width: 100px;
            height: 100px;
            flex-shrink: 0;
        }

        .gauge {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .gauge-bg {
            fill: none;
            stroke: var(--border-card);
            stroke-width: 8;
        }

        .gauge-val {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 251.2;
            stroke-dashoffset: calc(251.2 - (251.2 * {$averageScore}) / 100);
            transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gauge-val.grade-a { stroke: var(--color-success); }
        .gauge-val.grade-b { stroke: var(--color-warning); }
        .gauge-val.grade-c { stroke: var(--color-critical); }

        .gauge-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: var(--font-primary);
            font-size: 1.5rem;
            font-weight: 800;
        }

        .metric-desc h2 {
            font-family: var(--font-primary);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .metric-desc p {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        /* Summary lists */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            width: 100%;
        }

        .stat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-card);
        }
        body.light-theme .stat-item {
            background: rgba(0, 0, 0, 0.02);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stat-val {
            font-weight: 700;
            font-size: 0.95rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot.critical { background-color: var(--color-critical); }
        .dot.error { background-color: var(--color-error); }
        .dot.warning { background-color: var(--color-warning); }
        .dot.info { background-color: var(--color-info); }

        /* Filter Toolbar */
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
        }

        .search-group {
            position: relative;
            flex: 2;
            min-width: 250px;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-card);
            border-radius: 10px;
            padding: 10px 16px 10px 42px;
            color: var(--text-primary);
            font-family: var(--font-secondary);
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s ease;
        }
        body.light-theme .search-input {
            background: rgba(0, 0, 0, 0.02);
        }

        .search-input:focus {
            border-color: var(--color-primary);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }
        body.light-theme .search-input:focus {
            background: #fff;
        }

        .page-select-group {
            flex: 1;
            min-width: 200px;
        }

        .select-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-card);
            border-radius: 10px;
            padding: 10px 16px;
            color: var(--text-primary);
            font-family: var(--font-secondary);
            font-size: 0.9rem;
            outline: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        body.light-theme .select-input {
            background: rgba(0, 0, 0, 0.02);
        }
        body.light-theme .select-input option {
            background-color: white;
            color: #111827;
        }
        body.light-theme .select-input:focus {
            background-color: white;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-btn {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-card);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        body.light-theme .filter-btn {
            background: rgba(0, 0, 0, 0.02);
        }

        .filter-btn:hover, .filter-btn:focus-visible {
            color: var(--text-primary);
            border-color: var(--text-secondary);
            outline: none;
        }

        .filter-btn.active {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        /* Violations List */
        .violations-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .violation-card {
            border-left: 5px solid var(--color-primary);
            animation: slideIn 0.3s ease forwards;
        }

        .violation-card[data-severity="critical"] { border-left-color: var(--color-critical); }
        .violation-card[data-severity="error"] { border-left-color: var(--color-error); }
        .violation-card[data-severity="warning"] { border-left-color: var(--color-warning); }
        .violation-card[data-severity="info"] { border-left-color: var(--color-info); }

        .card-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border-card);
            padding-bottom: 12px;
        }

        .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .rule-badge {
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        body.light-theme .rule-badge {
            background: rgba(99, 102, 241, 0.08);
            color: var(--color-primary);
        }

        .standard-badge {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
            border: 1px solid var(--border-card);
        }
        body.light-theme .standard-badge {
            background: rgba(0, 0, 0, 0.04);
        }

        .severity-badge.severity-critical { background: var(--bg-critical); color: var(--color-critical); }
        .severity-badge.severity-error { background: var(--bg-error); color: var(--color-error); }
        .severity-badge.severity-warning { background: var(--bg-warning); color: var(--color-warning); }
        .severity-badge.severity-info { background: var(--bg-info); color: var(--color-info); }

        .severity-badge .dot {
            width: 6px;
            height: 6px;
            background-color: currentColor;
        }

        .location-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .icon-location {
            width: 14px;
            height: 14px;
        }

        .violation-msg {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 16px;
            line-height: 1.4;
        }

        /* Tabs and code blocks */
        .code-viewer {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-card);
            border-radius: 10px;
            overflow: hidden;
        }
        body.light-theme .code-viewer {
            background: #1e293b;
        }

        .tab-list {
            display: flex;
            background: rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid var(--border-card);
        }

        .tab-btn {
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            color: #9ca3af;
            padding: 10px 16px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            color: white;
            background: rgba(255, 255, 255, 0.02);
        }

        .tab-btn.active {
            color: white;
            border-bottom-color: var(--color-primary);
            background: rgba(255, 255, 255, 0.04);
        }

        .tab-panel {
            padding: 16px;
        }

        .tab-panel[hidden] {
            display: none;
        }

        pre {
            overflow-x: auto;
            max-height: 250px;
        }

        code {
            font-family: var(--font-code);
            font-size: 0.85rem;
            color: #e2e8f0;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .ai-recommendation {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ai-explanation {
            font-size: 0.85rem;
            line-height: 1.5;
            color: #34d399;
            background: rgba(16, 185, 129, 0.08);
            border-left: 3px solid var(--color-success);
            padding: 10px 14px;
            border-radius: 0 6px 6px 0;
        }

        .code-wrapper {
            position: relative;
        }

        .copy-btn {
            position: absolute;
            right: 8px;
            top: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .copy-btn:hover, .copy-btn:focus-visible {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
            outline: none;
        }

        .icon-copy {
            width: 14px;
            height: 14px;
        }

        /* empty state */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
            text-align: center;
            gap: 16px;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            color: var(--color-success);
        }

        .empty-state h3 {
            font-family: var(--font-primary);
            font-size: 1.3rem;
            font-weight: 700;
        }

        .empty-state p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            max-width: 400px;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-group {
                justify-content: stretch;
            }
            .filter-btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Accessibility Live Announcer -->
    <div id="a11y-announcer" class="sr-only" aria-live="polite" aria-atomic="true"></div>

    <div class="container">
        <!-- Header -->
        <header class="glass-panel app-header">
            <div class="brand-section">
                <div class="logo-container" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        <path d="M2 12h20"></path>
                    </svg>
                </div>
                <div class="title-group">
                    <h1>YakNet Accessibility Dashboard</h1>
                    <p>Web Standartları ve WCAG Uyum Analiz Raporu</p>
                </div>
            </div>
            <div class="header-meta">
                <div class="meta-tag" title="{$target}">Hedef Site: {$target}</div>
                <div class="meta-tag">Tarih: {$date}</div>
                <button id="theme-toggle" class="theme-btn" aria-label="Açık/Karanlık tema değiştir" onclick="toggleTheme()">
                    <!-- Moon icon -->
                    <svg class="icon-theme" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Metrics Grid -->
        <section class="metrics-grid" aria-label="Erişilebilirlik Özet Metrikleri">
            <!-- Health Score Card -->
            <div class="glass-panel metric-card">
                <div class="gauge-container">
                    <svg viewBox="0 0 100 100" class="gauge">
                        <circle cx="50" cy="50" r="40" class="gauge-bg"></circle>
                        <circle cx="50" cy="50" r="40" class="gauge-val" id="score-gauge" stroke-dasharray="251.2" stroke-dashoffset="251.2"></circle>
                    </svg>
                    <div class="gauge-label" id="score-val">0</div>
                </div>
                <div class="metric-desc">
                    <h2>Ortalama Sağlık Skoru</h2>
                    <p>Tarama yapılan sayfaların ortalama puanı.</p>
                </div>
            </div>

            <!-- Severity Distribution Card -->
            <div class="glass-panel">
                <div class="summary-stats">
                    <div class="stat-item">
                        <span class="stat-label">
                            <span class="dot critical"></span> Critical
                        </span>
                        <span class="stat-val">{$criticalCount}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <span class="dot error"></span> Error
                        </span>
                        <span class="stat-val">{$errorCount}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <span class="dot warning"></span> Warning
                        </span>
                        <span class="stat-val">{$warningCount}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">
                            <span class="dot info"></span> Info
                        </span>
                        <span class="stat-val">{$infoCount}</span>
                    </div>
                </div>
            </div>

            <!-- Standards Card -->
            <div class="glass-panel">
                <div class="summary-stats">
                    <div class="stat-item">
                        <span class="stat-label">Taranan Sayfa</span>
                        <span class="stat-val">{$totalPages}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">WCAG A / AA / AAA</span>
                        <span class="stat-val">{$standardA} / {$standardAA} / {$standardAAA}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Toplam İhlal</span>
                        <span class="stat-val">{$totalViolations}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filter Toolbar -->
        <section class="glass-panel toolbar" aria-label="Rapor Filtreleri">
            <div class="search-group">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="search" id="search-box" class="search-input" placeholder="Kural veya hata açıklaması ara..." aria-label="Kural veya hata açıklaması ara" oninput="applyFilters()">
            </div>

            <div class="page-select-group">
                <select id="page-select" class="select-input" aria-label="Taranan sayfaya göre filtrele" onchange="applyFilters()">
                    {$pageOptions}
                </select>
            </div>
            
            <div class="filter-group">
                <button class="filter-btn active" id="btn-all" onclick="setSeverityFilter('all')" aria-label="Tüm ihlalleri göster" aria-pressed="true">Hepsi</button>
                <button class="filter-btn" id="btn-critical" onclick="setSeverityFilter('critical')" aria-label="Sadece Critical seviye ihlalleri göster" aria-pressed="false">
                    <span class="dot critical"></span> Critical
                </button>
                <button class="filter-btn" id="btn-error" onclick="setSeverityFilter('error')" aria-label="Sadece Error seviye ihlalleri göster" aria-pressed="false">
                    <span class="dot error"></span> Error
                </button>
                <button class="filter-btn" id="btn-warning" onclick="setSeverityFilter('warning')" aria-label="Sadece Warning seviye ihlalleri göster" aria-pressed="false">
                    <span class="dot warning"></span> Warning
                </button>
                <button class="filter-btn" id="btn-info" onclick="setSeverityFilter('info')" aria-label="Sadece Info seviye ihlalleri göster" aria-pressed="false">
                    <span class="dot info"></span> Info
                </button>
            </div>
        </section>

        <!-- Violations Container -->
        <main class="violations-list" id="violations-container" aria-label="Erişilebilirlik İhlalleri Listesi">
            {$cards}
            
            <!-- Empty State -->
            <div class="glass-panel empty-state" id="empty-state" style="display: none;">
                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <h3>Harika Haber!</h3>
                <p>Belirtilen filtre kriterlerine uygun hiçbir erişilebilirlik hatası bulunamadı.</p>
            </div>
        </main>
    </div>

    <!-- JavaScript Logic -->
    <script>
        // Apply Circular Score Gauge Color and Animation
        const score = {$averageScore};
        const gauge = document.getElementById('score-gauge');
        const scoreText = document.getElementById('score-val');
        
        // Gauge stroke animation
        const totalLength = 251.2;
        const offset = totalLength - (totalLength * score) / 100;
        
        // Add proper grade class to gauge stroke
        if (score >= 90) {
            gauge.classList.add('grade-a');
        } else if (score >= 70) {
            gauge.classList.add('grade-b');
        } else {
            gauge.classList.add('grade-c');
        }
        
        // Trigger smooth stroke draw animation
        setTimeout(() => {
            gauge.style.strokeDashoffset = offset;
        }, 100);

        // Animate counter text
        let count = 0;
        const interval = setInterval(() => {
            if (count >= score) {
                scoreText.textContent = score;
                clearInterval(interval);
            } else {
                count += Math.ceil((score - count) / 10) || 1;
                scoreText.textContent = count > score ? score : count;
            }
        }, 30);

        // Tab Switching Logic with Accessibility Compliance
        document.querySelectorAll('.tab-list').forEach(tablist => {
            tablist.addEventListener('click', e => {
                const clickedTab = e.target.closest('[role="tab"]');
                if (!clickedTab) return;

                const tablistContainer = clickedTab.parentNode;
                const container = tablistContainer.parentNode;

                // Deactivate all tabs in this list
                tablistContainer.querySelectorAll('[role="tab"]').forEach(tab => {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                    tab.setAttribute('tabindex', '-1');
                });

                // Activate clicked tab
                clickedTab.classList.add('active');
                clickedTab.setAttribute('aria-selected', 'true');
                clickedTab.removeAttribute('tabindex');

                // Toggle panels
                const targetPanelId = clickedTab.getAttribute('aria-controls');
                container.querySelectorAll('[role="tabpanel"]').forEach(panel => {
                    if (panel.id === targetPanelId) {
                        panel.removeAttribute('hidden');
                        panel.classList.add('active');
                    } else {
                        panel.setAttribute('hidden', '');
                        panel.classList.remove('active');
                    }
                });
            });
            
            // Handle Keyboard Navigation for Tabs
            tablist.addEventListener('keydown', e => {
                const tabs = Array.from(tablist.querySelectorAll('[role="tab"]'));
                const index = tabs.indexOf(document.activeElement);
                if (index < 0) return;

                let nextIndex = index;
                if (e.key === 'ArrowRight') {
                    nextIndex = (index + 1) % tabs.length;
                } else if (e.key === 'ArrowLeft') {
                    nextIndex = (index - 1 + tabs.length) % tabs.length;
                } else {
                    return;
                }

                e.preventDefault();
                tabs[nextIndex].focus();
                tabs[nextIndex].click();
            });
        });

        // Theme Toggle Logic
        function toggleTheme() {
            const body = document.body;
            const themeBtn = document.getElementById('theme-toggle');
            const isDark = !body.classList.contains('light-theme');
            
            if (isDark) {
                body.classList.add('light-theme');
                themeBtn.innerHTML = `
                    <!-- Sun icon -->
                    <svg class="icon-theme" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                `;
                localStorage.setItem('theme', 'light');
                announce('Açık tema aktifleştirildi.');
            } else {
                body.classList.remove('light-theme');
                themeBtn.innerHTML = `
                    <!-- Moon icon -->
                    <svg class="icon-theme" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                `;
                localStorage.setItem('theme', 'dark');
                announce('Karanlık tema aktifleştirildi.');
            }
        }

        // Load persisted theme
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-theme');
            document.getElementById('theme-toggle').innerHTML = `
                <!-- Sun icon -->
                <svg class="icon-theme" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            `;
        }

        // Accessibility Screen Reader Announcer function
        function announce(message) {
            const announcer = document.getElementById('a11y-announcer');
            if (announcer) {
                announcer.textContent = '';
                setTimeout(() => {
                    announcer.textContent = message;
                }, 50);
            }
        }

        // Copy Suggestion to Clipboard with voice/aria announcer
        function copyCode(selector, btn) {
            const codeEl = document.querySelector(selector);
            if (!codeEl) return;

            navigator.clipboard.writeText(codeEl.textContent).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span style="color:#10b981;">Kopyalandı!</span>
                `;
                btn.setAttribute('aria-label', 'Kod kopyalandı.');
                announce('Önerilen kod düzeltmesi panoya kopyalandı.');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.removeAttribute('aria-label');
                }, 2000);
            }).catch(err => {
                console.error('Kopyalama hatası:', err);
            });
        }

        // Filtering Logic
        let activeSeverity = 'all';

        function setSeverityFilter(severity) {
            activeSeverity = severity;
            
            // Update button states with aria-pressed
            document.querySelectorAll('.filter-btn').forEach(btn => {
                const isSelected = btn.id === 'btn-' + severity;
                btn.classList.toggle('active', isSelected);
                btn.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
            });
            
            applyFilters();
        }

        function applyFilters() {
            const searchVal = document.getElementById('search-box').value.toLowerCase();
            const pageVal = document.getElementById('page-select').value;
            const cards = document.querySelectorAll('.violation-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const severity = card.getAttribute('data-severity');
                const rule = card.getAttribute('data-rule').toLowerCase();
                const message = card.getAttribute('data-message').toLowerCase();
                const page = card.getAttribute('data-page');

                const matchesSeverity = (activeSeverity === 'all' || severity === activeSeverity);
                const matchesSearch = (!searchVal || rule.includes(searchVal) || message.includes(searchVal));
                const matchesPage = (pageVal === 'all' || page === pageVal);

                if (matchesSeverity && matchesSearch && matchesPage) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show or hide empty state
            const emptyState = document.getElementById('empty-state');
            if (visibleCount === 0) {
                emptyState.style.display = 'flex';
                announce('Filtre sonucu eşleşen hiçbir hata bulunamadı.');
            } else {
                emptyState.style.display = 'none';
                announce(visibleCount + ' adet erişilebilirlik hatası gösteriliyor.');
            }
        }
    </script>
</body>
</html>
HTML;
    }
}
