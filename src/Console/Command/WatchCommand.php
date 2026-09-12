<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\BaselineManager;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Reporting\ConsoleFormatter;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class WatchCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('watch')
            ->setDescription('Continuously watch templates and files for changes and automatically re-scan')
            ->addArgument('target', InputArgument::OPTIONAL, 'The file or directory path to watch (defaults to config paths)')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to configuration file (default: a11y.yaml)')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Accessibility scan level (1-5)')
            ->addOption('interval', 'i', InputOption::VALUE_REQUIRED, 'Watch poll interval in seconds', '1')
            ->addOption('max-iterations', null, InputOption::VALUE_REQUIRED, 'Max loop iterations (used for testing/debugging)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configPathOption = $input->getOption('config');
        $configPath = is_string($configPathOption) ? $configPathOption : getcwd() . '/a11y.yaml';
        $config = Config::fromYaml($configPath);

        $levelOption = $input->getOption('level');
        $level = is_numeric($levelOption) ? (int)$levelOption : $config->getLevel();

        $target = $input->getArgument('target');
        $paths = $config->getPaths();
        if (is_string($target) && $target !== '') {
            $paths = [$target];
        }

        $intervalOption = $input->getOption('interval');
        $interval = is_numeric($intervalOption) ? (int)$intervalOption : 1;
        if ($interval < 1) {
            $interval = 1;
        }

        $maxIterationsOption = $input->getOption('max-iterations');
        $maxIterations = is_numeric($maxIterationsOption) ? (int)$maxIterationsOption : null;

        $io->title("Accessibility Console - Real-Time Watch Mode");
        $io->writeln("<fg=gray>Watching paths:</> <fg=cyan>" . implode(', ', $paths) . "</>");
        $io->writeln("<fg=gray>Rule Level:</> <fg=yellow>$level</>");
        $io->writeln("<fg=gray>Poll Interval:</> <fg=yellow>{$interval}s</>");
        $io->note("Press Ctrl+C to stop watching.");

        // Build Scanner
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($level);
        foreach ($rules as $rule) {
            $scanner->addRule($rule);
        }

        $analyser = new Analyser($config, $scanner, new BaselineManager());

        // Perform initial scan
        $io->section("Initial Scan");
        $this->runScanAndPrint($analyser, $paths, $io);

        // Map existing files to their last modified timestamp
        $fileMtimes = $this->collectFileMtimes($paths, $config->getExcludePaths());

        $iteration = 0;
        while (true) {
            if ($maxIterations !== null && $iteration >= $maxIterations) {
                break;
            }
            $iteration++;

            sleep($interval);

            $currentMtimes = $this->collectFileMtimes($paths, $config->getExcludePaths());
            $changedFiles = $this->detectChangedFiles($fileMtimes, $currentMtimes);

            if (!empty($changedFiles)) {
                $fileMtimes = $currentMtimes;
                $timestamp = date('H:i:s');
                $io->newLine();
                $io->writeln(sprintf(
                    "<fg=yellow>[%s]</> Change detected in <fg=cyan>%s</>. Re-scanning...",
                    $timestamp,
                    basename($changedFiles[0]) . (count($changedFiles) > 1 ? ' (+' . (count($changedFiles) - 1) . ' more)' : '')
                ));

                // Re-scan changed files or paths
                $this->runScanAndPrint($analyser, $changedFiles, $io);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @param Analyser $analyser
     * @param string[] $paths
     * @param SymfonyStyle $io
     */
    private function runScanAndPrint(Analyser $analyser, array $paths, SymfonyStyle $io): void
    {
        $start = microtime(true);
        $result = $analyser->analyse($paths);
        $elapsed = round(microtime(true) - $start, 3);

        $violations = $result['violations'];
        $formatter = new ConsoleFormatter();
        $formatter->format($violations, $result['baselinedCount'], $io);

        $metrics = AccessibilityScoreCalculator::calculate($violations);
        $score = $metrics['score'];
        $color = $score >= 90 ? 'green' : ($score >= 70 ? 'yellow' : 'red');

        $io->writeln(sprintf(
            "Health Score: <fg=%s;options=bold>%d/100</> | Scan time: <fg=gray>%ss</>",
            $color,
            $score,
            $elapsed
        ));
    }

    /**
     * @param string[] $paths
     * @param string[] $excludePaths
     * @return array<string, int>
     */
    public function collectFileMtimes(array $paths, array $excludePaths): array
    {
        $mtimes = [];
        $extensions = ['php', 'html', 'twig', 'blade.php'];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $mtimes[$path] = (int)filemtime($path);
                continue;
            }

            if (!is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $filename = $file->getFilename();
                $ext = $file->getExtension();
                $isMatching = in_array($ext, ['php', 'html', 'twig'], true) || str_ends_with($filename, '.blade.php');

                if (!$isMatching) {
                    continue;
                }

                $filePath = (string)$file->getRealPath();
                $excluded = false;
                foreach ($excludePaths as $ex) {
                    if (str_contains(str_replace('\\', '/', $filePath), '/' . trim($ex, '/') . '/')) {
                        $excluded = true;
                        break;
                    }
                }

                if (!$excluded) {
                    $mtimes[$filePath] = (int)$file->getMTime();
                }
            }
        }

        return $mtimes;
    }

    /**
     * @param array<string, int> $oldMtimes
     * @param array<string, int> $newMtimes
     * @return string[]
     */
    public function detectChangedFiles(array $oldMtimes, array $newMtimes): array
    {
        $changed = [];

        foreach ($newMtimes as $file => $mtime) {
            if (!isset($oldMtimes[$file]) || $oldMtimes[$file] !== $mtime) {
                $changed[] = $file;
            }
        }

        foreach ($oldMtimes as $file => $mtime) {
            if (!isset($newMtimes[$file])) {
                $changed[] = $file;
            }
        }

        return array_values(array_unique($changed));
    }
}
