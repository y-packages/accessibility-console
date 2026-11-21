<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\CI\CiWorkflowGenerator;

class InitCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription('Initialize a default a11y.yaml configuration file and optional CI/CD workflow')
            ->addOption('ci', null, InputOption::VALUE_REQUIRED, 'Generate CI/CD workflow template (github, gitlab, azure, bitbucket)')
            ->addOption('preset', 'p', InputOption::VALUE_REQUIRED, 'Default compliance preset for generated CI workflow', 'wcag22-aa');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Optional CI/CD pipeline workflow scaffolding
        $ciOption = $input->getOption('ci');
        if (is_string($ciOption) && $ciOption !== '') {
            $preset = is_string($input->getOption('preset')) ? $input->getOption('preset') : 'wcag22-aa';
            try {
                $generator = new CiWorkflowGenerator();
                $yaml = $generator->generate($ciOption, $preset);
                $relPath = CiWorkflowGenerator::getDefaultPath($ciOption);
                $fullPath = getcwd() . '/' . $relPath;
                $dir = dirname($fullPath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }
                file_put_contents($fullPath, $yaml);
                $io->success("Generated CI/CD workflow ({$ciOption}) at: {$relPath}");
            } catch (\InvalidArgumentException $e) {
                $io->error($e->getMessage());
                return Command::FAILURE;
            }
        }

        $configPath = getcwd() . '/a11y.yaml';

        if (file_exists($configPath)) {
            $io->warning("Configuration file 'a11y.yaml' already exists in the current directory.");
            return Command::SUCCESS;
        }

        $defaultConfig = <<<YAML
# YakNet Accessibility Console Configuration
# For detailed configuration, see: https://forum.yak.net.tr

# Paths to scan (can be directories or specific files)
paths:
  - .

# Exclude directories or files
exclude_paths:
  - vendor
  - node_modules
  - tests
  - storage
  - .git

# Accessibility scanning level (1-5, default 4)
level: 4

# Optional regulatory or sectoral compliance preset
# Available: section508, en301549, eaa2025, bitv2, wcag22-aa, wcag22-aaa,
#            government, healthcare, banking, education, ecommerce
# preset: wcag22-aa

# Output format (console, json, github, gitlab, sarif, junit, checkstyle, markdown, csv, xml)
format: console

# File extensions to analyse (Blade, Twig, Vue, JSX/TSX, PHP, HTML)
extensions:
  - php
  - html
  - htm
  - twig
  - blade.php
  - vue
  - jsx
  - tsx

# Path to the baseline file for ignoring existing violations
# baseline: a11y-baseline.json

# Governance policy thresholds (enforced automatically during `a11y scan`)
# policy:
#   min_score: 90
#   max_critical: 0
#   max_errors: 0
#   max_warnings: 10
#   required_standards:
#     - A
#   banned_rules:
#     - WCAG_1_1_1_ALT
#     - WCAG_2_3_1_FLASHING_CONTENT
#   severity_overrides:
#     WCAG_2_5_8_TARGET_SIZE: error
#   principle_limits:
#     Perceivable: 0

# Optional CI/CD webhook notifications
# notifications:
#   slack_webhook: ""
#   discord_webhook: ""
#   teams_webhook: ""
#   webhook_url: ""
#   webhook_secret: ""

# Rule adjustments
rules:
  # Rules to exclude from analysis
  exclude:
    # - WCAG_1_3_1_FIELDSET
  # Additional custom rules to run
  include:
    # - App\Rules\MyCustomRule
YAML;

        if (file_put_contents($configPath, $defaultConfig) !== false) {
            $io->success("Created configuration file: a11y.yaml");
            return Command::SUCCESS;
        }

        $io->error("Failed to create configuration file 'a11y.yaml'.");
        return Command::FAILURE;
    }
}
