<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\HtmlDirValid;

class HtmlDirValidTest extends TestCase
{
    public function testFlagsInvalidDir(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new HtmlDirValid());

        $violations = $scanner->scan('<div><p dir="left">Paragraph</p></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_2_HTML_DIR', $violations[0]->ruleId);
    }

    public function testPassesValidDir(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new HtmlDirValid());

        $violations = $scanner->scan('<div><p dir="rtl">Arabic text</p><p dir="ltr">English</p><p dir="auto">Auto</p></div>');
        $this->assertCount(0, $violations);
    }
}
