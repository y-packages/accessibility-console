<?php

namespace YakNet\AccessibilityConsole\Tests\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use YakNet\AccessibilityConsole\Console\Command\WatchCommand;

class WatchCommandTest extends TestCase
{
    public function testWatchCommandRunsInitialScanAndExitsWithMaxIterations(): void
    {
        $command = new WatchCommand();
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'target' => 'demo/views-fixed/home.php',
            '--max-iterations' => '1',
            '--interval' => '1',
        ]);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('Accessibility Console - Real-Time Watch Mode', $output);
        $this->assertStringContainsString('Initial Scan', $output);
        $this->assertStringContainsString('Health Score:', $output);
    }

    public function testDetectChangedFiles(): void
    {
        $command = new WatchCommand();

        $old = [
            'file1.php' => 100,
            'file2.php' => 200,
        ];

        $new = [
            'file1.php' => 100,
            'file2.php' => 205, // modified
            'file3.php' => 300, // added
        ];

        $changed = $command->detectChangedFiles($old, $new);
        $this->assertContains('file2.php', $changed);
        $this->assertContains('file3.php', $changed);
        $this->assertNotContains('file1.php', $changed);
    }
}
