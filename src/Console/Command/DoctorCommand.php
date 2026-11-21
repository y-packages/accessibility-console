<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\RuleRegistry;
use YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class DoctorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('doctor')
            ->setDescription('Diagnose environment, rule registry integrity, optional integrations, and configuration health')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to a11y.yaml configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('YakNet Accessibility Console - System & Integrity Doctor');

        $hasFailures = false;
        $rows = [];

        // 1. PHP Version
        $phpOk = version_compare(PHP_VERSION, '8.2.0', '>=');
        $rows[] = ['PHP Runtime', PHP_VERSION, $phpOk ? '<fg=green>OK</>' : '<fg=red>FAIL (>= 8.2 required)</>'];
        if (!$phpOk) {
            $hasFailures = true;
        }

        // 2. Required Extensions
        foreach (['dom', 'libxml', 'mbstring', 'json'] as $ext) {
            $loaded = extension_loaded($ext);
            $rows[] = ["PHP ext-{$ext}", $loaded ? 'Loaded' : 'Missing', $loaded ? '<fg=green>OK</>' : '<fg=red>FAIL</>'];
            if (!$loaded) {
                $hasFailures = true;
            }
        }

        // 3. Rule Registry Validation
        $rules = RuleLevels::getRulesForLevel(9);
        $validation = RuleRegistry::validate($rules);
        $rows[] = [
            'Rule Registry Integrity',
            sprintf('%d rules registered', $validation['count']),
            $validation['valid'] ? '<fg=green>VALID (0 conflicts)</>' : '<fg=red>ERRORS (' . count($validation['errors']) . ')</>',
        ];
        if (!$validation['valid']) {
            $hasFailures = true;
        }

        // 4. Compliance Presets
        $presets = (new PresetRegistry())->all();
        $rows[] = ['Compliance Presets', sprintf('%d profiles available', count($presets)), '<fg=green>OK</>'];

        // 5. Offline Self-Healing Coverage
        $healableCount = count(LocalHealer::getSupportedRuleIds());
        $rows[] = ['Offline Self-Healer', sprintf('%d deterministic auto-fixes', $healableCount), '<fg=green>OK</>'];

        // 6. Optional Exporters & Renderers
        $hasDompdf = class_exists(\Dompdf\Dompdf::class);
        $rows[] = ['PDF Report Generator (dompdf)', $hasDompdf ? 'Installed' : 'Not installed', $hasDompdf ? '<fg=green>READY</>' : '<fg=yellow>OPTIONAL</>'];

        $hasSpreadsheet = class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class);
        $rows[] = ['Excel Exporter (phpspreadsheet)', $hasSpreadsheet ? 'Installed' : 'Not installed', $hasSpreadsheet ? '<fg=green>READY</>' : '<fg=yellow>OPTIONAL</>'];

        $hasChrome = class_exists(\HeadlessChromium\BrowserFactory::class);
        $rows[] = ['Headless Chrome Renderer', $hasChrome ? 'Installed' : 'Not installed', $hasChrome ? '<fg=green>READY</>' : '<fg=yellow>OPTIONAL</>'];

        // 7. Configuration & AI Key
        $configOption = $input->getOption('config');
        $configPath = is_string($configOption) ? $configOption : getcwd() . '/a11y.yaml';
        $configExists = file_exists($configPath);
        $config = Config::fromYaml($configPath);
        $rows[] = ['Configuration (a11y.yaml)', $configExists ? $configPath : 'Using built-in defaults', '<fg=green>OK</>'];

        $hasAiKey = $config->getGeminiApiKey() !== null && $config->getGeminiApiKey() !== '';
        $rows[] = ['AI Self-Healing (GEMINI_API_KEY)', $hasAiKey ? 'Configured' : 'Not set (offline healer active)', $hasAiKey ? '<fg=green>READY</>' : '<fg=yellow>OFFLINE MODE</>'];

        $table = new Table($output);
        $table->setHeaders(['Component', 'Details', 'Status']);
        $table->setRows($rows);
        $table->render();
        $io->newLine();

        if (!$validation['valid']) {
            foreach ($validation['errors'] as $err) {
                $io->error($err);
            }
        }

        if ($hasFailures) {
            $io->error('One or more critical system checks failed.');
            return Command::FAILURE;
        }

        $io->success('All critical checks passed. YakNet Accessibility Console is ready.');
        return Command::SUCCESS;
    }
}
