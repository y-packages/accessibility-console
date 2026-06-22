<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription('Initialize a default a11y.yaml configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
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

# Accessibility scanning level (1-9, default 4)
level: 4

# Output format (console, json, html, github)
format: console

# Path to the baseline file for ignoring existing violations
# baseline: a11y-baseline.json

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
