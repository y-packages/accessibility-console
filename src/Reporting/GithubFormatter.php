<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class GithubFormatter implements FormatterInterface
{
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        if (empty($violations)) {
            return Command::SUCCESS;
        }

        foreach ($violations as $v) {
            $file = $v->location['file'] ?? '';
            $line = $v->location['line'] ?? 1;
            
            // Normalize path separators to forward slashes for Github Actions compatibility
            $file = str_replace('\\', '/', $file);
            
            $type = ($v->severity->value === 'error') ? 'error' : 'warning';
            
            $io->writeln(sprintf(
                "::%s file=%s,line=%d::[%s] %s",
                $type,
                $file,
                $line,
                $v->ruleId,
                $v->message
            ));
        }

        return Command::FAILURE;
    }
}
