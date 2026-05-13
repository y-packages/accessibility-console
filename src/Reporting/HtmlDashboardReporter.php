<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;

class HtmlDashboardReporter
{
    /**
     * @param Violation[] $violations
     */
    public function render(array $violations, string $target): string
    {
        $count = count($violations);
        $date = date('Y-m-d H:i:s');
        
        $rows = '';
        foreach ($violations as $v) {
            $severityClass = strtolower($v->severity->value);
            $rows .= "
            <tr class='severity-{$severityClass}'>
                <td><span class='badge'>{$v->ruleId}</span></td>
                <td>{$v->message}</td>
                <td><pre><code>" . htmlspecialchars($v->htmlSnippet) . "</code></pre></td>
                <td>" . ($v->location ? "{$v->location['file']}:{$v->location['line']}" : 'N/A') . "</td>
            </tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>A11y Report - {$target}</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { color: #2c3e50; margin-top: 0; }
        .summary { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { background: #3498db; color: white; padding: 20px; border-radius: 8px; flex: 1; text-align: center; }
        .card.error { background: #e74c3c; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        pre { background: #272822; color: #f8f8f2; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        .badge { background: #34495e; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
        .severity-error { background: #fff5f5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Accessibility Report</h1>
        <p>Target: <strong>{$target}</strong> | Generated: {$date}</p>
        
        <div class="summary">
            <div class="card error">
                <h2>{$count}</h2>
                <p>Violations Found</p>
            </div>
            <div class="card">
                <h2>WCAG 2.1</h2>
                <p>Standard Level</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Rule ID</th>
                    <th>Issue</th>
                    <th>Code Snippet</th>
                    <th>Source Location</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
    </div>
</body>
</html>
HTML;
    }
}
