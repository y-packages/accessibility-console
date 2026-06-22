<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;

interface FormatterInterface
{
    /**
     * Format and output the analysis violations.
     *
     * @param Violation[] $violations
     * @param int $baselinedCount
     * @param SymfonyStyle $io
     * @return int Exit code (Command::SUCCESS or Command::FAILURE)
     */
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int;
}
