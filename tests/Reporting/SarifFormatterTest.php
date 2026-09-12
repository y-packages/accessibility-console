<?php

namespace YakNet\AccessibilityConsole\Tests\Reporting;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;
use YakNet\AccessibilityConsole\Reporting\SarifFormatter;

class SarifFormatterTest extends TestCase
{
    public function testRendersValidSarifJson(): void
    {
        $formatter = new SarifFormatter();
        $violations = [
            new Violation(
                'WCAG_1_1_1_IMG_ALT',
                'Images must have alternative text.',
                Severity::ERROR,
                WCAGStandard::A,
                '<img src="test.jpg">',
                ['file' => 'templates/home.blade.php', 'line' => 14, 'column' => 5],
                'Add alt="" attribute'
            ),
            new Violation(
                'WCAG_1_4_3_CONTRAST',
                'Insufficient color contrast.',
                Severity::WARNING,
                WCAGStandard::AA,
                '<span style="color:#ccc">Text</span>',
                ['file' => 'templates/home.blade.php', 'line' => 20, 'column' => 1]
            ),
        ];

        $json = $formatter->render($violations);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertSame('2.1.0', $decoded['version']);
        $this->assertSame('YakNet Accessibility Console', $decoded['runs'][0]['tool']['driver']['name']);
        $this->assertCount(2, $decoded['runs'][0]['results']);

        $firstResult = $decoded['runs'][0]['results'][0];
        $this->assertSame('WCAG_1_1_1_IMG_ALT', $firstResult['ruleId']);
        $this->assertSame('error', $firstResult['level']);
        $this->assertSame('templates/home.blade.php', $firstResult['locations'][0]['physicalLocation']['artifactLocation']['uri']);
        $this->assertSame(14, $firstResult['locations'][0]['physicalLocation']['region']['startLine']);
    }

    public function testFormatOutputsToSymfonyStyle(): void
    {
        $formatter = new SarifFormatter();
        $violations = [];

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $io = new SymfonyStyle($input, $output);

        $exitCode = $formatter->format($violations, 0, $io);
        $this->assertSame(0, $exitCode);

        $content = $output->fetch();
        $this->assertJson($content);
        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        $this->assertEmpty($decoded['runs'][0]['results']);
    }
}
