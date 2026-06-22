<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class RulesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('rules')
            ->setDescription('List all available accessibility scanning rules');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Accessibility Console - Available Rules");

        $table = new Table($output);
        $table->setHeaders(['Rule ID', 'Standard', 'Severity', 'Level', 'Description']);

        $rules = RuleLevels::getRulesForLevel(9);
        foreach ($rules as $rule) {
            $ruleId = ($rule instanceof \YakNet\AccessibilityConsole\Rules\RuleInterface) 
                ? $rule->getId() 
                : (new \ReflectionClass($rule))->getShortName();
                
            $description = method_exists($rule, 'getDescription') ? $rule->getDescription() : '';
            $standard = method_exists($rule, 'getStandard') ? $rule->getStandard()->value : '';
            $severity = method_exists($rule, 'getSeverity') ? $rule->getSeverity()->value : '';
            $level = method_exists($rule, 'getLevel') ? $rule->getLevel() : 4;
            
            $table->addRow([
                "<fg=cyan;options=bold>$ruleId</>",
                $standard,
                $severity,
                $level,
                $description
            ]);
        }

        $table->render();
        $io->newLine();
        $io->note("Total rules available: " . count($rules));

        return Command::SUCCESS;
    }
}
