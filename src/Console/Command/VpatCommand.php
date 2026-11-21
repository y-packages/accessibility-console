<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Compliance\VpatReportGenerator;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\BaselineManager;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class VpatCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('vpat')
            ->setDescription('Generate a VPAT 2.5 Accessibility Conformance Report (WCAG 2.1/2.2, Section 508, EN 301 549)')
            ->addArgument('target', InputArgument::OPTIONAL, 'Target file or directory path to evaluate')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to a11y.yaml configuration file')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Rule level (1-5)', '5')
            ->addOption('preset', 'p', InputOption::VALUE_REQUIRED, 'Compliance preset (e.g. wcag22-aa, en301549, section508, government)')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (markdown, html, json)', 'markdown')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Write the VPAT 2.5 report to a file path')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Product or application name', 'Web Application')
            ->addOption('product-version', null, InputOption::VALUE_REQUIRED, 'Product version string', '1.0.0')
            ->addOption('evaluator', null, InputOption::VALUE_REQUIRED, 'Evaluator name or team', 'YakNet Accessibility Console');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configOption = $input->getOption('config');
        $configPath = is_string($configOption) ? $configOption : getcwd() . '/a11y.yaml';
        $config = Config::fromYaml($configPath);

        $levelOption = $input->getOption('level');
        $level = is_numeric($levelOption) ? (int)$levelOption : 5;

        $presetOption = $input->getOption('preset');
        if (is_string($presetOption) && $presetOption !== '') {
            $preset = (new PresetRegistry())->get($presetOption);
            if ($preset !== null) {
                $level = $preset->getLevel();
            }
        }

        $target = $input->getArgument('target');
        $paths = is_string($target) && $target !== '' ? [$target] : $config->getPaths();

        $scanner = new Scanner();
        foreach (RuleLevels::getRulesForLevel($level) as $rule) {
            $scanner->addRule($rule);
        }

        $analyser = new Analyser($config, $scanner, new BaselineManager());
        $result = $analyser->analyse($paths);
        $violations = $result['violations'];

        $product = is_string($input->getOption('product')) ? $input->getOption('product') : 'Web Application';
        $productVersion = is_string($input->getOption('product-version')) ? $input->getOption('product-version') : '1.0.0';
        $evaluator = is_string($input->getOption('evaluator')) ? $input->getOption('evaluator') : 'YakNet Accessibility Console';
        $format = strtolower(is_string($input->getOption('format')) ? $input->getOption('format') : 'markdown');

        $generator = new VpatReportGenerator();
        $rendered = match ($format) {
            'html' => $generator->renderHtml($violations, $product, $productVersion, $evaluator),
            'json' => $generator->renderJson($violations, $product, $productVersion, $evaluator),
            default => $generator->renderMarkdown($violations, $product, $productVersion, $evaluator),
        };

        $outputFile = $input->getOption('output');
        if (is_string($outputFile) && $outputFile !== '') {
            $dir = dirname($outputFile);
            if (!is_dir($dir) && $dir !== '.') {
                @mkdir($dir, 0775, true);
            }
            file_put_contents($outputFile, $rendered);
            $io->success(sprintf('VPAT 2.5 report (%s) saved to: %s', strtoupper($format), $outputFile));
            return Command::SUCCESS;
        }

        $output->write($rendered);
        return Command::SUCCESS;
    }
}
