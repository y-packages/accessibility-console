<?php

namespace YakNet\AccessibilityConsole\Tests\Reporting;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;
use YakNet\AccessibilityConsole\Reporting\JunitFormatter;

class JunitFormatterTest extends TestCase
{
    public function testRendersValidJunitXmlWithViolations(): void
    {
        $formatter = new JunitFormatter();
        $violations = [
            new Violation(
                'WCAG_1_1_1_IMG_ALT',
                'Images must have alternative text.',
                Severity::ERROR,
                WCAGStandard::A,
                '<img src="photo.png">',
                ['file' => 'views/index.php', 'line' => 10, 'column' => 2]
            ),
        ];

        $xml = $formatter->render($violations);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<testsuites', $xml);
        $this->assertStringContainsString('failures="1"', $xml);
        $this->assertStringContainsString('<failure', $xml);
        $this->assertStringContainsString('WCAG_1_1_1_IMG_ALT', $xml);

        // Verify valid XML parsing
        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($xml));
    }

    public function testRendersCleanXmlWhenEmpty(): void
    {
        $formatter = new JunitFormatter();
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $io = new SymfonyStyle($input, $output);

        $exitCode = $formatter->format([], 0, $io);
        $this->assertSame(0, $exitCode);

        $xml = $output->fetch();
        $this->assertStringContainsString('failures="0"', $xml);
        $this->assertStringContainsString('AccessibilityCompliant', $xml);
    }
}
