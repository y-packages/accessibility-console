<?php

namespace YakNet\AccessibilityConsole\Integrations;

class GitHubActionsReporter
{
    /**
     * Format WCAG violations as GitHub Actions Workflow Command Annotations.
     *
     * @param array<string, mixed> $scanResult
     * @param string $fileName
     * @return string Formatted annotations string
     */
    public static function formatAnnotations(array $scanResult, string $fileName = 'index.html'): string
    {
        /** @var array<int, array<string, mixed>> $violations */
        $violations = is_array($scanResult['violations'] ?? null) ? $scanResult['violations'] : [];
        $lines = [];

        foreach ($violations as $v) {
            $ruleId = is_string($v['ruleId'] ?? null) ? $v['ruleId'] : 'WCAG';
            $desc = is_string($v['description'] ?? null) ? $v['description'] : '';
            $line = is_numeric($v['line'] ?? null) ? intval($v['line']) : 1;
            $severity = is_string($v['severity'] ?? null) ? strtolower($v['severity']) : 'error';
            $level = $severity === 'error' ? 'error' : 'warning';

            $lines[] = sprintf('::%s file=%s,line=%d::[%s] %s', $level, $fileName, $line, $ruleId, $desc);
        }

        return implode("\n", $lines);
    }
}
