<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class MarkdownFormatter implements FormatterInterface
{
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $md = [];
        $md[] = "# Accessibility Scan Report";
        $md[] = "";
        $md[] = sprintf("- **Total Violations:** %d", count($violations));
        $md[] = sprintf("- **Baselined Violations:** %d", $baselinedCount);
        $md[] = "";
        $md[] = "## Detailed Violations";
        $md[] = "";
        
        if (empty($violations)) {
            $md[] = "*No violations found!* 🎉";
        } else {
            foreach ($violations as $index => $v) {
                $file = $v->location['file'] ?? 'unknown';
                $line = $v->location['line'] ?? 0;
                $md[] = sprintf("### %d. %s [%s - %s]", $index + 1, $v->ruleId, strtoupper($v->severity->value), $v->standard->value);
                $md[] = sprintf("- **File:** `%s` on line %d", $file, $line);
                $md[] = sprintf("- **Message:** %s", $v->message);
                if ($v->fixSuggestion) {
                    $md[] = sprintf("- **Fix Suggestion:** %s", $v->fixSuggestion);
                }
                $md[] = "- **Snippet:**";
                $md[] = "  ```html";
                $md[] = "  " . trim($v->htmlSnippet);
                $md[] = "  ```";
                $md[] = "";
            }
        }
        
        $io->write(implode("\n", $md));
        
        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }
}
