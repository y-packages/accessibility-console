<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class ConsoleFormatter implements FormatterInterface
{
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        if (empty($violations)) {
            $io->success("No violations found!");
            if ($baselinedCount > 0) {
                $io->comment("($baselinedCount violations ignored by baseline)");
            }
            return Command::SUCCESS;
        }

        // Group by file
        $grouped = [];
        foreach ($violations as $v) {
            $file = $v->location['file'] ?? 'unknown';
            $grouped[$file][] = $v;
        }

        // Sort files alphabetically for stable order
        ksort($grouped);

        foreach ($grouped as $file => $fileViolations) {
            $io->section("File: " . $file);
            
            // Sort violations by line number
            usort($fileViolations, function ($a, $b) {
                return ($a->location['line'] ?? 0) <=> ($b->location['line'] ?? 0);
            });

            foreach ($fileViolations as $v) {
                $line = $v->location['line'] ?? 0;
                
                $severityColor = 'red';
                if ($v->severity->value === 'warning') {
                    $severityColor = 'yellow';
                } elseif ($v->severity->value === 'info') {
                    $severityColor = 'cyan';
                }

                // Format the output
                $io->writeln(sprintf(
                    "  <fg=gray>Line %d:</> <fg=%s;options=bold>[%s]</> %s",
                    $line,
                    $severityColor,
                    $v->ruleId,
                    $v->message
                ));
                
                // Show snippet
                $snippet = (string)preg_replace('/\s+/', ' ', trim($v->htmlSnippet));
                $io->writeln("    <fg=gray>Snippet:</> <fg=yellow>" . htmlspecialchars($snippet) . "</>");
                
                if ($v->fixSuggestion !== null && $v->fixSuggestion !== '') {
                    $io->writeln("    <fg=gray>AI Suggestion:</> <fg=green>" . htmlspecialchars(trim($v->fixSuggestion)) . "</>");
                }
                $io->writeln("");
            }
        }

        $totalViolations = count($violations);
        $io->error(sprintf(
            "Found %d %s across %d %s.",
            $totalViolations,
            $totalViolations === 1 ? 'violation' : 'violations',
            count($grouped),
            count($grouped) === 1 ? 'file' : 'files'
        ));

        if ($baselinedCount > 0) {
            $io->comment("($baselinedCount violations ignored by baseline)");
        }

        return Command::FAILURE;
    }
}
