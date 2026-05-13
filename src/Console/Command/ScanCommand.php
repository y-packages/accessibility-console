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

class ScanCommand extends Command
{
    protected static $defaultName = 'scan';

    protected function configure(): void
    {
        $this
            ->setName('scan')
            ->setDescription('Scan a URL or file for accessibility violations')
            ->addArgument('target', InputArgument::REQUIRED, 'The URL or file path to scan')
            ->addOption('ai', null, InputOption::VALUE_NONE, 'Enable AI-powered fix suggestions')
            ->addOption('report', null, InputOption::VALUE_REQUIRED, 'Save report to file (html or json)')
            ->addOption('project-path', null, InputOption::VALUE_REQUIRED, 'Base path for source mapping');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $target = $input->getArgument('target');
        $enableAi = $input->getOption('ai');
        $projectPath = $input->getOption('project-path') ?? getcwd();

        $io->title("Accessibility Console - Scanning: $target");

        // 1. Fetch HTML
        $html = @file_get_contents($target);
        if (!$html) {
            $io->error("Could not read target: $target");
            return Command::FAILURE;
        }

        // 2. Setup Scanner
        $scanner = new Scanner();
        foreach (StandardRuleSet::all() as $rule) {
            $scanner->addRule($rule);
        }

        // 3. Scan
        $violations = $scanner->scan($html);

        if (empty($violations)) {
            $io->success("No violations found!");
            return Command::SUCCESS;
        }

        $io->section("Found " . count($violations) . " violations");

        // 4. Setup Helpers
        $locator = new SourceLocator($projectPath);
        $config = new Config();
        $fixer = $enableAi ? new GeminiFixer($config->getGeminiApiKey()) : null;

        foreach ($violations as $violation) {
            $io->writeln("<fg=white;bg=red;options=bold> {$violation->ruleId} </>\t<fg=red>{$violation->message}</>");
            
            // Show Snippet
            $io->writeln("  <fg=gray>Snippet:</> <fg=yellow>" . htmlspecialchars($violation->htmlSnippet) . "</>");

            // Source Mapping
            $location = $locator->locate($violation->htmlSnippet);
            if ($location) {
                $io->writeln("  <fg=gray>Source:</>  <fg=cyan>{$location['file']}:{$location['line']}</>");
            }

            // AI Fixer
            if ($enableAi && $fixer) {
                $io->write("  <fg=gray>AI Fix Suggestion:</> ");
                $suggestion = $fixer->suggestFix($violation);
                if ($suggestion) {
                    $violation->fixSuggestion = $suggestion;
                    $io->writeln("<fg=green>" . htmlspecialchars($suggestion) . "</>");
                } else {
                    $io->writeln("<fg=red>Could not generate suggestion (check API key)</>");
                }
            }

            $io->writeln("");
        }

        $io->warning("Scanned " . count($violations) . " errors. Use --ai to get fix suggestions.");

        // Export Report
        $reportPath = $input->getOption('report');
        if ($reportPath) {
            $extension = pathinfo($reportPath, PATHINFO_EXTENSION);
            if ($extension === 'json') {
                $data = array_map(fn($v) => $v->toArray(), $violations);
                file_put_contents($reportPath, json_encode($data, JSON_PRETTY_PRINT));
            } else {
                $reporter = new \YakNet\AccessibilityConsole\Reporting\HtmlDashboardReporter();
                file_put_contents($reportPath, $reporter->render($violations, $target));
            }
            $io->success("Report saved to: $reportPath");
        }

        return Command::SUCCESS;
    }
}
