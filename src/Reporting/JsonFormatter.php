<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class JsonFormatter implements FormatterInterface
{
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $serialized = [];
        foreach ($violations as $v) {
            $serialized[] = $v->toArray();
        }

        $result = [
            'totals' => [
                'violations' => count($violations),
                'baselined' => $baselinedCount
            ],
            'violations' => $serialized
        ];

        $io->write((string)json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }
}
