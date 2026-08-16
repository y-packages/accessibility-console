<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\HtmlSyntaxValid;

class HtmlSyntaxValidTest extends TestCase
{
    public function testFlagsDuplicateAttributesInHtml(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new HtmlSyntaxValid());

        $violations = $scanner->scan('<div><input id="email" type="email" id="email2"></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_1_HTML_PARSING_SYNTAX', $violations[0]->ruleId);
    }

    public function testPassesValidHtml(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new HtmlSyntaxValid());

        $violations = $scanner->scan('<div><input id="email" type="email" name="user_email"></div>');
        $this->assertCount(0, $violations);
    }
}
