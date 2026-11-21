<?php

namespace YakNet\AccessibilityConsole\Core\Container;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Console\Command\CompareCommand;
use YakNet\AccessibilityConsole\Console\Command\DoctorCommand;
use YakNet\AccessibilityConsole\Console\Command\ExplainCommand;
use YakNet\AccessibilityConsole\Console\Command\FixCommand;
use YakNet\AccessibilityConsole\Console\Command\InitCommand;
use YakNet\AccessibilityConsole\Console\Command\PresetsCommand;
use YakNet\AccessibilityConsole\Console\Command\RulesCommand;
use YakNet\AccessibilityConsole\Console\Command\ScanCommand;
use YakNet\AccessibilityConsole\Console\Command\VpatCommand;
use YakNet\AccessibilityConsole\Console\Command\WatchCommand;

class ContainerFactory
{
    /**
     * Create and configure the PHP-DI container.
     *
     * @return ContainerInterface
     */
    public static function create(): ContainerInterface
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Config::class => function () {
                $configPath = getcwd() . '/a11y.yaml';
                return Config::fromYaml($configPath);
            },
            Scanner::class => function () {
                return new Scanner();
            },
            Analyser::class => function (ContainerInterface $c) {
                $config = $c->get(Config::class);
                $scanner = $c->get(Scanner::class);
                return new Analyser(
                    $config instanceof Config ? $config : null,
                    $scanner instanceof Scanner ? $scanner : null
                );
            },
            ScanCommand::class => function () {
                return new ScanCommand();
            },
            FixCommand::class => function () {
                return new FixCommand();
            },
            InitCommand::class => function () {
                return new InitCommand();
            },
            RulesCommand::class => function () {
                return new RulesCommand();
            },
            WatchCommand::class => function () {
                return new WatchCommand();
            },
            VpatCommand::class => function () {
                return new VpatCommand();
            },
            CompareCommand::class => function () {
                return new CompareCommand();
            },
            DoctorCommand::class => function () {
                return new DoctorCommand();
            },
            ExplainCommand::class => function () {
                return new ExplainCommand();
            },
            PresetsCommand::class => function () {
                return new PresetsCommand();
            },
        ]);

        return $builder->build();
    }
}
