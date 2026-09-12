<?php

namespace YakNet\AccessibilityConsole\Tests\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use YakNet\AccessibilityConsole\Console\Command\RulesCommand;

class RulesCommandTest extends TestCase
{
    public function testListAllRules(): void
    {
        $command = new RulesCommand();
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('Accessibility Console - Available Rules', $output);
        $this->assertStringContainsString('Total rules available: 130', $output);
    }

    public function testFilterRulesBySearch(): void
    {
        $command = new RulesCommand();
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--search' => 'contrast',
        ]);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('WCAG_1_4_3_CONTRAST', $output);
        $this->assertStringContainsString('Showing', $output);
    }

    public function testFilterRulesByStandardAndLevel(): void
    {
        $command = new RulesCommand();
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--standard' => 'AA',
        ]);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('AA', $output);
        $this->assertStringContainsString('Showing', $output);
    }
}
