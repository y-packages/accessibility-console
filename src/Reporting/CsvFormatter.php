<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class CsvFormatter implements FormatterInterface
{
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $out = fopen('php://temp', 'w+');
        if ($out === false) {
            return Command::FAILURE;
        }
        
        // Write header
        fputcsv($out, ['Rule ID', 'Severity', 'Standard', 'File', 'Line', 'Message', 'HTML Snippet', 'Fix Suggestion'], ',', '"', '\\');
        
        foreach ($violations as $v) {
            $file = $v->location['file'] ?? 'unknown';
            $line = $v->location['line'] ?? 0;
            fputcsv($out, [
                $v->ruleId,
                $v->severity->value,
                $v->standard->value,
                $file,
                $line,
                $v->message,
                $v->htmlSnippet,
                $v->fixSuggestion
            ], ',', '"', '\\');
        }
        
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        
        $io->write($csv ?: '');
        
        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }
}
