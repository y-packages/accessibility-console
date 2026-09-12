<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

use Symfony\Component\Console\Input\InputOption;

class RulesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('rules')
            ->setDescription('List all available accessibility scanning rules')
            ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'Search rules by ID or description')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Filter rules by maximum level (1-5)')
            ->addOption('standard', null, InputOption::VALUE_REQUIRED, 'Filter rules by standard (A, AA, AAA)')
            ->addOption('severity', null, InputOption::VALUE_REQUIRED, 'Filter rules by severity (error, warning, info)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Accessibility Console - Available Rules");

        $searchQuery = $input->getOption('search');
        $levelFilter = $input->getOption('level');
        $standardFilter = $input->getOption('standard');
        $severityFilter = $input->getOption('severity');

        $maxLevel = is_numeric($levelFilter) ? (int)$levelFilter : 9;
        $rules = RuleLevels::getRulesForLevel($maxLevel);

        $table = new Table($output);
        $table->setHeaders(['Rule ID', 'Standard', 'Severity', 'Level', 'Description']);

        $matchedCount = 0;
        foreach ($rules as $rule) {
            $ruleId = ($rule instanceof \YakNet\AccessibilityConsole\Rules\RuleInterface) 
                ? $rule->getId() 
                : (new \ReflectionClass($rule))->getShortName();
                
            $description = method_exists($rule, 'getDescription') ? $rule->getDescription() : '';
            $standard = method_exists($rule, 'getStandard') ? $rule->getStandard()->value : '';
            $severity = method_exists($rule, 'getSeverity') ? $rule->getSeverity()->value : '';
            $level = method_exists($rule, 'getLevel') ? $rule->getLevel() : 4;

            // Search filter
            if (is_string($searchQuery) && $searchQuery !== '') {
                $query = strtolower($searchQuery);
                if (!str_contains(strtolower($ruleId), $query) && !str_contains(strtolower($description), $query)) {
                    continue;
                }
            }

            // Standard filter
            if (is_string($standardFilter) && $standardFilter !== '') {
                if (strcasecmp($standard, $standardFilter) !== 0) {
                    continue;
                }
            }

            // Severity filter
            if (is_string($severityFilter) && $severityFilter !== '') {
                if (strcasecmp($severity, $severityFilter) !== 0) {
                    continue;
                }
            }
            
            $table->addRow([
                "<fg=cyan;options=bold>$ruleId</>",
                $standard,
                $severity,
                $level,
                $description
            ]);
            $matchedCount++;
        }

        $table->render();
        $io->newLine();

        if (is_string($searchQuery) || $levelFilter !== null || is_string($standardFilter) || is_string($severityFilter)) {
            $io->note(sprintf("Showing %d matching rules (out of %d total rules).", $matchedCount, count(RuleLevels::getRulesForLevel(9))));
        } else {
            $io->note("Total rules available: " . count($rules));
        }

        return Command::SUCCESS;
    }
}
