<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Core;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\BaselineManager;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Core\Suppression\InlineSuppressionParser;
use YakNet\AccessibilityConsole\Rules\ImgAltText;

class InlineSuppressionParserTest extends TestCase
{
    public function testParsesNextLineBlockAndFileDirectivesWithReasons(): void
    {
        $parser = new InlineSuppressionParser();
        $html = <<<HTML
<div>
<!-- a11y-disable-next-line WCAG_1_1_1_ALT -- Decorative hero sprite -->
<img src="hero.png">
<!-- a11y-disable WCAG_1_4_3_CONTRAST -- Brand theme -->
<p style="color:#aaa;background:#fff;">Muted</p>
<!-- a11y-enable WCAG_1_4_3_CONTRAST -->
<p>Normal</p>
</div>
HTML;

        $parsed = $parser->parse($html);

        $this->assertSame('Decorative hero sprite', $parser->getSuppressionReason($parsed, 'WCAG_1_1_1_ALT', 3));
        $this->assertNull($parser->getSuppressionReason($parsed, 'WCAG_1_1_1_ALT', 4));
        $this->assertSame('Brand theme', $parser->getSuppressionReason($parsed, 'WCAG_1_4_3_CONTRAST', 5));
        $this->assertNull($parser->getSuppressionReason($parsed, 'WCAG_1_4_3_CONTRAST', 7));
    }

    public function testAnalyserHonorsInlineSuppressionAndLogsAuditTrail(): void
    {
        $tmpFile = sys_get_temp_dir() . '/yaknet_suppression_' . uniqid() . '.html';
        file_put_contents($tmpFile, "<div>\n<!-- a11y-disable-next-line WCAG_1_1_1_ALT -- Approved decorative -->\n<img src=\"a.png\">\n<img src=\"b.png\">\n</div>");

        try {
            $scanner = new Scanner();
            $scanner->addRule(new ImgAltText());
            $analyser = new Analyser(new Config(), $scanner, new BaselineManager());

            $result = $analyser->analyse([$tmpFile]);
            $this->assertCount(1, $result['violations']);

            $auditLog = $analyser->getSuppressedAuditLog();
            $this->assertCount(1, $auditLog);
            $this->assertSame('WCAG_1_1_1_ALT', $auditLog[0]['ruleId']);
            $this->assertSame('Approved decorative', $auditLog[0]['reason']);
        } finally {
            @unlink($tmpFile);
        }
    }
}
