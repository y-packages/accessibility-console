<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use YakNet\AccessibilityConsole\Console\Command\CompareCommand;
use YakNet\AccessibilityConsole\Console\Command\DoctorCommand;
use YakNet\AccessibilityConsole\Console\Command\ExplainCommand;
use YakNet\AccessibilityConsole\Console\Command\PresetsCommand;
use YakNet\AccessibilityConsole\Console\Command\RulesCommand;
use YakNet\AccessibilityConsole\Console\Command\VpatCommand;

class V4CommandsTest extends TestCase
{
    public function testDoctorCommandPasses(): void
    {
        $tester = new CommandTester(new DoctorCommand());
        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('175 rules registered', $output);
        $this->assertStringContainsString('VALID (0 conflicts)', $output);
        $this->assertStringContainsString('11 profiles available', $output);
    }

    public function testExplainCommandDisplaysRuleAndWcagGuidance(): void
    {
        $tester = new CommandTester(new ExplainCommand());
        $exitCode = $tester->execute(['rule' => 'WCAG_4_1_2_ARIA_TABLIST']);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('WCAG_4_1_2_ARIA_TABLIST', $output);
        $this->assertStringContainsString('W3C WCAG Guidance', $output);
        $this->assertStringContainsString('Non-Compliant Example', $output);
    }

    public function testPresetsCommandListsAllElevenPresets(): void
    {
        $tester = new CommandTester(new PresetsCommand());
        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('wcag22-aa', $output);
        $this->assertStringContainsString('eaa2025', $output);
        $this->assertStringContainsString('government', $output);
        $this->assertStringContainsString('Total compliance presets available: 11', $output);
    }

    public function testRulesCommandHealableAndPrincipleFilters(): void
    {
        $tester = new CommandTester(new RulesCommand());
        $exitCode = $tester->execute(['--healable' => true]);

        $this->assertSame(0, $exitCode);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('WCAG_3_2_2_BUTTON_TYPE', $output);
        $this->assertStringContainsString('Showing', $output);

        $exitCode2 = $tester->execute(['--principle' => 'Operable']);
        $this->assertSame(0, $exitCode2);
        $this->assertStringContainsString('WCAG_2_', $tester->getDisplay());
    }

    public function testVpatCommandOutputsMarkdownReport(): void
    {
        $tmpHtml = sys_get_temp_dir() . '/yaknet_vpat_' . uniqid() . '.html';
        file_put_contents($tmpHtml, '<!DOCTYPE html><html lang="en"><head><title>Demo</title></head><body><main><h1>Hello</h1><img src="x.png"></main></body></html>');

        try {
            $tester = new CommandTester(new VpatCommand());
            $exitCode = $tester->execute([
                'target' => $tmpHtml,
                '--format' => 'markdown',
                '--product' => 'YakNet Demo',
            ]);

            $this->assertSame(0, $exitCode);
            $output = $tester->getDisplay();
            $this->assertStringContainsString('# Accessibility Conformance Report (VPAT 2.5)', $output);
            $this->assertStringContainsString('YakNet Demo', $output);
        } finally {
            @unlink($tmpHtml);
        }
    }

    public function testCompareCommandDetectsRegressionAndFailsWhenRequested(): void
    {
        $beforeFile = sys_get_temp_dir() . '/yaknet_before_' . uniqid() . '.json';
        $afterFile = sys_get_temp_dir() . '/yaknet_after_' . uniqid() . '.json';

        file_put_contents($beforeFile, json_encode([]));
        file_put_contents($afterFile, json_encode([
            ['ruleId' => 'WCAG_1_1_1_ALT', 'message' => 'Missing alt', 'severity' => 'error', 'htmlSnippet' => '<img src="new.png">', 'location' => ['file' => 'index.html', 'line' => 10]],
        ]));

        try {
            $tester = new CommandTester(new CompareCommand());
            $exitCode = $tester->execute([
                'before' => $beforeFile,
                'after' => $afterFile,
                '--fail-on-regression' => true,
            ]);

            $this->assertSame(1, $exitCode);
            $this->assertStringContainsString('REGRESSION', $tester->getDisplay());
            $this->assertStringContainsString('WCAG_1_1_1_ALT', $tester->getDisplay());
        } finally {
            @unlink($beforeFile);
            @unlink($afterFile);
        }
    }
}
