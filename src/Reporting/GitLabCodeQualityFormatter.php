<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\Severity;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class GitLabCodeQualityFormatter implements FormatterInterface
{
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $quality = [];
        
        foreach ($violations as $v) {
            $file = $v->location['file'] ?? 'unknown';
            $line = $v->location['line'] ?? 1;
            
            // Map our severity to GitLab code quality severities:
            // info, minor, major, critical, blocker
            $gitLabSeverity = 'major';
            if ($v->severity === Severity::CRITICAL) {
                $gitLabSeverity = 'critical';
            } elseif ($v->severity === Severity::WARNING) {
                $gitLabSeverity = 'minor';
            }
            
            $quality[] = [
                'description' => sprintf('[%s] %s', $v->ruleId, $v->message),
                'fingerprint' => md5($v->ruleId . ':' . $file . ':' . $line . ':' . $v->htmlSnippet),
                'severity' => $gitLabSeverity,
                'location' => [
                    'path' => $file,
                    'lines' => [
                        'begin' => $line
                    ]
                ]
            ];
        }
        
        $io->write((string)json_encode($quality, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }
}
