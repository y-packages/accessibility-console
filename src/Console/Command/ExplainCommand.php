<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\RuleRegistry;
use YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer;
use YakNet\AccessibilityConsole\Reference\WcagDatabase;
use YakNet\AccessibilityConsole\Rules\RuleController;

class ExplainCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('explain')
            ->setDescription('Display detailed documentation, WCAG reference, and code examples for a specific rule ID')
            ->addArgument('rule', InputArgument::REQUIRED, 'Rule ID or class short name (e.g. WCAG_2_5_8_TARGET_SIZE)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $query = $input->getArgument('rule');
        if (!is_string($query) || trim($query) === '') {
            $io->error('Please provide a rule ID to explain.');
            return Command::FAILURE;
        }

        $controller = new RuleController();
        $rule = $controller->getRule($query);

        // Case-insensitive or short-name fallback lookup
        if ($rule === null) {
            foreach (\YakNet\AccessibilityConsole\Rules\RuleLevels::getRulesForLevel(9) as $candidate) {
                $meta = RuleRegistry::getMetadata($candidate);
                $candId = is_scalar($meta['id'] ?? null) ? (string)$meta['id'] : '';
                $candShort = is_scalar($meta['shortName'] ?? null) ? (string)$meta['shortName'] : '';
                if (strcasecmp($candId, $query) === 0 || strcasecmp($candShort, $query) === 0) {
                    $rule = $candidate;
                    break;
                }
            }
        }

        if ($rule === null) {
            $io->error("Rule '{$query}' not found. Run `a11y rules --search={$query}` to search available rules.");
            return Command::FAILURE;
        }

        $meta = RuleRegistry::getMetadata($rule);
        $ruleId = is_scalar($meta['id'] ?? null) ? (string)$meta['id'] : '';
        $className = is_scalar($meta['class'] ?? null) ? (string)$meta['class'] : '';
        $type = is_scalar($meta['type'] ?? null) ? (string)$meta['type'] : 'element';
        $standard = is_scalar($meta['standard'] ?? null) ? (string)$meta['standard'] : 'A';
        $severity = is_scalar($meta['severity'] ?? null) ? (string)$meta['severity'] : 'error';
        $level = is_scalar($meta['level'] ?? null) ? (string)$meta['level'] : '4';
        $description = is_scalar($meta['description'] ?? null) ? (string)$meta['description'] : '';

        $wcag = WcagDatabase::get($ruleId);
        $isHealable = in_array($ruleId, LocalHealer::getSupportedRuleIds(), true);

        $principle = 'Robust';
        if (preg_match('/WCAG_([1-4])_/', $ruleId, $m)) {
            $principle = match ((int)$m[1]) {
                1 => 'Perceivable',
                2 => 'Operable',
                3 => 'Understandable',
                default => 'Robust',
            };
        }

        $io->title("Rule Reference: {$ruleId}");
        $io->definitionList(
            ['Class' => $className],
            ['Type' => ucfirst($type) . '-level rule'],
            ['WCAG Level' => 'Level ' . $standard],
            ['POUR Principle' => $principle],
            ['Severity' => strtoupper($severity)],
            ['Scan Level' => 'Level ' . $level],
            ['Offline Auto-Fix' => $isHealable ? 'Supported (`a11y fix --offline`)' : 'Manual / AI (`a11y fix`)'],
            ['Description' => $description]
        );

        if ($wcag !== null) {
            $io->section('W3C WCAG Guidance');
            $io->writeln("<fg=cyan>Criterion:</> {$wcag->title} (Level {$wcag->level})");
            $io->writeln("<fg=cyan>Reference:</> {$wcag->url}");
            $io->newLine();
            $io->writeln("<fg=red;options=bold>Non-Compliant Example:</>");
            $io->writeln("  " . $wcag->badExample);
            $io->newLine();
            $io->writeln("<fg=green;options=bold>Compliant Example:</>");
            $io->writeln("  " . $wcag->goodExample);
            $io->newLine();
        }

        return Command::SUCCESS;
    }
}
