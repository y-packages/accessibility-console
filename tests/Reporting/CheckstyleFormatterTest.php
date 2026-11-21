<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Reporting;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;
use YakNet\AccessibilityConsole\Reporting\CheckstyleFormatter;

class CheckstyleFormatterTest extends TestCase
{
    public function testRendersValidCheckstyleXml(): void
    {
        $formatter = new CheckstyleFormatter();
        $violations = [
            new Violation(
                ruleId: 'WCAG_1_1_1_ALT',
                message: 'Image missing alt attribute',
                severity: Severity::ERROR,
                standard: WCAGStandard::A,
                htmlSnippet: '<img src="a.png">',
                location: ['file' => 'views/home.blade.php', 'line' => 14, 'column' => 5]
            ),
            new Violation(
                ruleId: 'WCAG_2_5_8_TARGET_SIZE',
                message: 'Button too small',
                severity: Severity::WARNING,
                standard: WCAGStandard::AA,
                htmlSnippet: '<button>X</button>',
                location: ['file' => 'views/home.blade.php', 'line' => 22, 'column' => 3]
            ),
        ];

        $xml = $formatter->render($violations);

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<checkstyle version="4.0.0">', $xml);
        $this->assertStringContainsString('<file name="views/home.blade.php">', $xml);
        $this->assertStringContainsString('source="YakNet.Accessibility.WCAG_1_1_1_ALT"', $xml);
        $this->assertStringContainsString('severity="warning"', $xml);

        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($xml));
    }
}
