<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\AI\AiFixerManager;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\RuleLevels;
use YakNet\AccessibilityConsole\Source\SourceLocator;

class FixCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('fix')
            ->setDescription('Automatically fix accessibility violations using AI')
            ->addArgument('target', InputArgument::OPTIONAL, 'The URL, file, or directory path to scan and fix')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration file (default: a11y.yaml)')
            ->addOption('project-path', null, InputOption::VALUE_REQUIRED, 'Base path for source mapping (URL scans only)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be fixed without modifying files')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Accessibility scan level (1-9)')
            ->addOption('js', null, InputOption::VALUE_NONE, 'Enable JavaScript rendering using Headless Chrome (URL scans only)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // 1. Resolve configuration
        $configPathOption = $input->getOption('config');
        $configPath = is_string($configPathOption) ? $configPathOption : getcwd() . '/a11y.yaml';
        $config = Config::fromYaml($configPath);

        $apiKey = $config->getGeminiApiKey();
        if (!$apiKey) {
            $io->error("GEMINI_API_KEY not found in .env or environment variables. AI fixing requires an API key.");
            return Command::FAILURE;
        }

        $levelOption = $input->getOption('level');
        $level = is_numeric($levelOption) ? (int)$levelOption : $config->getLevel();

        $target = $input->getArgument('target');
        $isUrl = is_string($target) && (str_starts_with($target, 'http://') || str_starts_with($target, 'https://'));

        // If target is a URL, run the legacy URL fixing logic
        if ($isUrl) {
            return $this->executeUrlFix($target, $level, $apiKey, $input, $output, $io);
        }

        // --- LOCAL FILE / DIRECTORY SELF-HEALING ENGINE ---
        $io->title("AI Self-Healing Mode - Directory Static Analysis");
        $io->writeln("<fg=gray>Configuration loaded from:</> <fg=cyan>$configPath</>");
        $dryRun = (bool)$input->getOption('dry-run');

        // Resolve paths to scan
        $paths = $config->getPaths();
        if (is_string($target) && $target !== '') {
            $paths = [$target];
        }

        // Setup Analyser
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($level);
        foreach ($rules as $rule) {
            $scanner->addRule($rule);
        }

        // We run without baseline filtering so we can fix ALL errors in the project,
        // or we can optionally load baseline. Fixing everything is better for a general "fix" run.
        $analyser = new Analyser($config, $scanner, new \YakNet\AccessibilityConsole\Core\BaselineManager());

        $io->note("Scanning targets for violations...");
        $result = $analyser->analyse($paths);
        $violations = $result['violations'];

        if (empty($violations)) {
            $io->success("Everything looks good! No violations to fix.");
            return Command::SUCCESS;
        }

        $io->note(sprintf("Found %d violations. Starting AI analysis and healing...", count($violations)));

        $fixer = new AiFixerManager($apiKey);
        $diffGenerator = new \YakNet\AccessibilityConsole\Core\Diff\VisualDiffGenerator();
        $fixedCount = 0;

        // Group violations by file path
        $groupedViolations = [];
        foreach ($violations as $v) {
            $file = $v->location['file'] ?? '';
            if ($file !== '') {
                $groupedViolations[$file][] = $v;
            }
        }

        foreach ($groupedViolations as $file => $fileViolations) {
            $io->section("Fixing file: $file");

            // Sort violations by line number DESCENDING (bottom to top)
            // This prevents edits at the bottom of the file from invalidating line numbers above them!
            usort($fileViolations, function ($a, $b) {
                return ($b->location['line'] ?? 0) <=> ($a->location['line'] ?? 0);
            });

            $absoluteFile = getcwd() . '/' . $file;
            if (!file_exists($absoluteFile) || !is_writable($absoluteFile)) {
                $io->warning("File not found or not writable: $file. Skipping.");
                continue;
            }

            $localHealer = new \YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer();

            foreach ($fileViolations as $violation) {
                $line = $violation->location['line'] ?? 0;
                $io->write("Analyzing <fg=yellow>{$violation->ruleId}</> on line <fg=cyan>$line</>... ");

                // Try Local Healer first
                $rawSuggestion = $localHealer->heal($violation);
                $isLocalFix = false;

                if ($rawSuggestion !== null) {
                    $isLocalFix = true;
                } else {
                    // Fallback to AI Suggestion
                    $rawSuggestion = $fixer->suggestFix($violation);
                }

                $suggestion = $rawSuggestion;
                if (is_string($rawSuggestion) && str_contains($rawSuggestion, 'FIX:')) {
                    preg_match('/FIX:(.*)/s', $rawSuggestion, $matches);
                    $suggestion = trim($matches[1] ?? $rawSuggestion);
                    $suggestion = preg_replace('/^```html\s*|\s*```$/i', '', $suggestion);
                }

                if (!is_string($suggestion)) {
                    $io->writeln("<fg=red>Skipped (AI could not generate a clean fix)</>");
                    continue;
                }

                if (is_string($rawSuggestion) && $suggestion === $rawSuggestion && str_contains($suggestion, 'EXPLANATION:')) {
                    $io->writeln("<fg=red>Skipped (AI could not generate a clean fix)</>");
                    continue;
                }

                if ($isLocalFix) {
                    $io->writeln("<fg=green>Local Fix Found!</>");
                } else {
                    $io->writeln("<fg=green>AI Fix Found!</>");
                }
                $io->writeln("  <fg=gray>Diff:</>");
                $diffOutput = $diffGenerator->generate($violation->htmlSnippet, $suggestion);
                $indentedDiff = implode("\n", array_map(fn($line) => '    ' . $line, explode("\n", trim($diffOutput))));
                $io->writeln($indentedDiff);

                if ($dryRun) {
                    $io->writeln("  <fg=blue>[DRY RUN] File modification skipped.</>");
                    continue;
                }

                // Apply Fix
                if ($this->applyFix($absoluteFile, $violation->htmlSnippet, $suggestion)) {
                    $io->writeln("  <fg=bright-green>✔ Applied to line $line</>");
                    $fixedCount++;
                } else {
                    $io->writeln("  <fg=red>✘ Failed to apply fix to file.</>");
                }
                $io->writeln("");
            }
        }

        if ($fixedCount > 0) {
            $io->success("Self-healing complete! $fixedCount violations were automatically fixed.");
        } else {
            $io->warning("No files were modified.");
        }

        return Command::SUCCESS;
    }

    /**
     * Legacy URL scan and fix logic
     */
    private function executeUrlFix(string $target, int $level, string $apiKey, InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        $projectPathOption = $input->getOption('project-path');
        $projectPath = is_string($projectPathOption) ? $projectPathOption : getcwd();
        if ($projectPath === false) {
            $projectPath = '.';
        }
        $dryRun = (bool)$input->getOption('dry-run');
        $useJs = (bool)$input->getOption('js');

        $io->title("AI Self-Healing Mode - Fixing URL: $target");

        // Fetch HTML
        if ($useJs) {
            try {
                $chromeRenderer = new \YakNet\AccessibilityConsole\Core\ChromeRenderer();
                $html = $chromeRenderer->render($target);
            } catch (\Throwable $e) {
                $io->error("Failed JavaScript rendering: " . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $html = @file_get_contents($target);
        }
        if ($html === false || $html === '') {
            $io->error("Could not read target URL: $target");
            return Command::FAILURE;
        }

        // Scan
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($level);
        foreach ($rules as $rule) {
            $scanner->addRule($rule);
        }
        $violations = $scanner->scan($html);

        if (empty($violations)) {
            $io->success("Everything looks good! No violations to fix.");
            return Command::SUCCESS;
        }

        $io->note("Found " . count($violations) . " violations. Starting AI analysis...");

        $fixer = new AiFixerManager($apiKey);
        $diffGenerator = new \YakNet\AccessibilityConsole\Core\Diff\VisualDiffGenerator();
        $locator = new SourceLocator($projectPath);
        $fixedCount = 0;

        foreach ($violations as $violation) {
            $io->write("Analyzing <fg=yellow>{$violation->ruleId}</>... ");

            // Locate source
            $location = $locator->locate($violation->htmlSnippet);
            if (!$location) {
                $io->writeln("<fg=red>Skipped (Source not found)</>");
                continue;
            }

            $localHealer = new \YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer();
            $rawSuggestion = $localHealer->heal($violation);
            $isLocalFix = false;

            if ($rawSuggestion !== null) {
                $isLocalFix = true;
            } else {
                $rawSuggestion = $fixer->suggestFix($violation);
            }

            $suggestion = $rawSuggestion;
            if (is_string($rawSuggestion) && str_contains($rawSuggestion, 'FIX:')) {
                preg_match('/FIX:(.*)/s', $rawSuggestion, $matches);
                $suggestion = trim($matches[1] ?? $rawSuggestion);
                $suggestion = preg_replace('/^```html\s*|\s*```$/i', '', $suggestion);
            }

            if (!is_string($suggestion)) {
                $io->writeln("<fg=red>Skipped (AI could not generate a clean fix)</>");
                continue;
            }

            if (is_string($rawSuggestion) && $suggestion === $rawSuggestion && str_contains($suggestion, 'EXPLANATION:')) {
                $io->writeln("<fg=red>Skipped (AI could not generate a clean fix)</>");
                continue;
            }

            if ($isLocalFix) {
                $io->writeln("<fg=green>Local Fix Found!</>");
            } else {
                $io->writeln("<fg=green>AI Fix Found!</>");
            }
            $io->writeln("  <fg=gray>Diff:</>");
            $diffOutput = $diffGenerator->generate($violation->htmlSnippet, $suggestion);
            $indentedDiff = implode("\n", array_map(fn($line) => '    ' . $line, explode("\n", trim($diffOutput))));
            $io->writeln($indentedDiff);

            if ($dryRun) {
                $io->writeln("  <fg=blue>[DRY RUN] File modification skipped.</>");
                continue;
            }

            // Apply Fix
            if ($this->applyFix($location['file'], $violation->htmlSnippet, $suggestion)) {
                $io->writeln("  <fg=bright-green>✔ Applied to {$location['file']}:{$location['line']}</>");
                $fixedCount++;
            } else {
                $io->writeln("  <fg=red>✘ Failed to apply fix to file.</>");
            }
            $io->writeln("");
        }

        if ($fixedCount > 0) {
            $io->success("Self-healing complete! $fixedCount violations were automatically fixed.");
        } else {
            $io->warning("No files were modified.");
        }

        return Command::SUCCESS;
    }

    private function applyFix(string $filePath, string $originalSnippet, string $fixedSnippet): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) return false;

        // Try exact replacement first
        if (str_contains($content, $originalSnippet)) {
            $newContent = str_replace($originalSnippet, $fixedSnippet, $content);
        } else {
            // Fallback: Fuzzy replacement
            $newContent = str_replace(trim($originalSnippet), trim($fixedSnippet), $content);
        }

        if ($newContent === $content) {
            return false;
        }

        return file_put_contents($filePath, $newContent) !== false;
    }
}
