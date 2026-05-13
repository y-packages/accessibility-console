<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\AI\GeminiFixer;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\StandardRuleSet;
use YakNet\AccessibilityConsole\Source\SourceLocator;

class FixCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('fix')
            ->setDescription('Automatically fix accessibility violations using AI')
            ->addArgument('target', InputArgument::REQUIRED, 'The URL or file path to scan')
            ->addOption('project-path', null, InputOption::VALUE_REQUIRED, 'Base path for source mapping')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be fixed without modifying files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $target = $input->getArgument('target');
        $projectPath = $input->getOption('project-path') ?? getcwd();
        $dryRun = $input->getOption('dry-run');

        $config = new Config();
        $apiKey = $config->getGeminiApiKey();

        if (!$apiKey) {
            $io->error("GEMINI_API_KEY not found in .env or environment variables. AI fixing requires an API key.");
            return Command::FAILURE;
        }

        $io->title("AI Self-Healing Mode - Fixing: $target");

        // 1. Fetch HTML
        $html = @file_get_contents($target);
        if (!$html) {
            $io->error("Could not read target: $target");
            return Command::FAILURE;
        }

        // 2. Scan
        $scanner = new Scanner();
        foreach (StandardRuleSet::all() as $rule) {
            $scanner->addRule($rule);
        }
        $violations = $scanner->scan($html);

        if (empty($violations)) {
            $io->success("Everything looks good! No violations to fix.");
            return Command::SUCCESS;
        }

        $io->note("Found " . count($violations) . " violations. Starting AI analysis...");

        $fixer = new GeminiFixer($apiKey);
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

            // Get AI Suggestion
            $suggestion = $fixer->suggestFix($violation);
            if (!$suggestion) {
                $io->writeln("<fg=red>Skipped (AI could not generate fix)</>");
                continue;
            }

            $io->writeln("<fg=green>Fix Found!</>");
            $io->writeln("  <fg=gray>Original:</> <fg=red>" . htmlspecialchars(trim($violation->htmlSnippet)) . "</>");
            $io->writeln("  <fg=gray>AI Fix:  </> <fg=green>" . htmlspecialchars(trim($suggestion)) . "</>");

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
            $io->warning("No files were modified. Check error logs for details.");
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
            // Fallback: Fuzzy replacement (simplified for this demo)
            // In a real professional lib, we'd use more advanced regex or DOM patching
            $newContent = str_replace(trim($originalSnippet), trim($fixedSnippet), $content);
        }

        if ($newContent === $content) {
            return false;
        }

        return file_put_contents($filePath, $newContent) !== false;
    }
}
