<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;

class PresetsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('presets')
            ->setDescription('List all available regulatory and sectoral compliance presets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('YakNet Accessibility Console - Compliance Presets');

        $registry = new PresetRegistry();
        $presets = $registry->all();

        $table = new Table($output);
        $table->setHeaders(['Preset Name', 'Rule Level', 'Priority Rules', 'Description']);

        foreach ($presets as $preset) {
            $table->addRow([
                '<fg=cyan;options=bold>' . $preset->getName() . '</>',
                'Level ' . $preset->getLevel(),
                (string)count($preset->getIncludedRules()),
                $preset->getDescription(),
            ]);
        }

        $table->render();
        $io->newLine();
        $io->note(sprintf('Total compliance presets available: %d. Use with `a11y scan --preset=<name>`.', count($presets)));

        return Command::SUCCESS;
    }
}
