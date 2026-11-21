<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Analytics\HistoryTracker;

class CompareCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('compare')
            ->setDescription('Compare two JSON accessibility scan reports to detect regressions and resolved violations')
            ->addArgument('before', InputArgument::REQUIRED, 'Path to the baseline/previous JSON report')
            ->addArgument('after', InputArgument::REQUIRED, 'Path to the new/current JSON report')
            ->addOption('fail-on-regression', null, InputOption::VALUE_NONE, 'Return non-zero exit code if new violations were introduced')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (console, json)', 'console');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $beforePath = $input->getArgument('before');
        $afterPath = $input->getArgument('after');
        if (!is_string($beforePath) || !is_string($afterPath)) {
            $io->error('Both "before" and "after" JSON report paths are required.');
            return Command::FAILURE;
        }

        $beforeData = $this->loadViolations($beforePath);
        $afterData = $this->loadViolations($afterPath);

        if ($beforeData === null) {
            $io->error("Could not read or parse JSON report: {$beforePath}");
            return Command::FAILURE;
        }
        if ($afterData === null) {
            $io->error("Could not read or parse JSON report: {$afterPath}");
            return Command::FAILURE;
        }

        $tracker = new HistoryTracker();
        $diff = $tracker->compareReports($beforeData, $afterData);

        $format = strtolower(is_string($input->getOption('format')) ? $input->getOption('format') : 'console');
        if ($format === 'json') {
            $output->writeln((string)json_encode($diff, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $io->title('YakNet Accessibility Console - Regression Comparison');
            $deltaSign = $diff['score_delta'] > 0 ? '+' : '';
            $deltaColor = $diff['score_delta'] > 0 ? 'green' : ($diff['score_delta'] < 0 ? 'red' : 'gray');

            $table = new Table($output);
            $table->setHeaders(['Metric', 'Before', 'After', 'Delta']);
            $table->addRows([
                ['Health Score', $diff['before_score'] . '/100', $diff['after_score'] . '/100', "<fg={$deltaColor}>{$deltaSign}{$diff['score_delta']}</>"],
                ['Total Violations', (string)$diff['before_count'], (string)$diff['after_count'], (string)($diff['after_count'] - $diff['before_count'])],
                ['New Regressions', '-', (string)count($diff['new_violations']), count($diff['new_violations']) > 0 ? '<fg=red>REGRESSION</>' : '<fg=green>NONE</>'],
                ['Resolved Issues', '-', (string)count($diff['resolved_violations']), count($diff['resolved_violations']) > 0 ? '<fg=green>IMPROVED</>' : '-'],
                ['Unchanged Issues', '-', (string)$diff['unchanged_count'], '-'],
            ]);
            $table->render();
            $io->newLine();

            if (!empty($diff['new_violations'])) {
                $io->section('Newly Introduced Violations (' . count($diff['new_violations']) . ')');
                foreach ($diff['new_violations'] as $v) {
                    $ruleId = is_scalar($v['ruleId'] ?? null) ? (string)$v['ruleId'] : 'UNKNOWN';
                    $msg = is_scalar($v['message'] ?? null) ? (string)$v['message'] : '';
                    $loc = (isset($v['location']) && is_array($v['location']) && is_scalar($v['location']['file'] ?? null))
                        ? (string)$v['location']['file'] . ':' . (is_numeric($v['location']['line'] ?? null) ? (int)$v['location']['line'] : 0)
                        : 'unknown';
                    $io->writeln("  <fg=red>+ [{$ruleId}]</> ({$loc}) {$msg}");
                }
            }
        }

        if ($input->getOption('fail-on-regression') && !empty($diff['new_violations'])) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function loadViolations(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        // Support both flat violation arrays and { violations: [...] } wrapped JSON formatters
        $list = (isset($decoded['violations']) && is_array($decoded['violations'])) ? $decoded['violations'] : $decoded;

        $result = [];
        foreach ($list as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $result[] = $item;
            }
        }
        return $result;
    }
}
