<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\PresentationTagsBanned;

class PresentationTagsBannedTest extends TestCase
{
    public function testFlagsBannedPresentationTags(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new PresentationTagsBanned());

        $violations = $scanner->scan('<div><center>Centered text</center><strike>Strikethrough</strike></div>');
        $this->assertCount(2, $violations);
        $this->assertSame('WCAG_1_3_1_PRESENTATION_TAGS', $violations[0]->ruleId);
        $this->assertSame('WCAG_1_3_1_PRESENTATION_TAGS', $violations[1]->ruleId);
    }

    public function testPassesCssStyling(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new PresentationTagsBanned());

        $violations = $scanner->scan('<div><span style="text-align: center; text-decoration: line-through;">Styled Text</span></div>');
        $this->assertCount(0, $violations);
    }
}
