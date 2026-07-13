<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\LinkTextUrl;

class LinkTextUrlTest extends TestCase
{
    public function testFlagsLinkWithRawUrlText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new LinkTextUrl());

        $violations = $scanner->scan('<div><a href="https://google.com">https://google.com</a></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_4_4_LINK_URL', $violations[0]->ruleId);
    }

    public function testPassesDescriptiveLinkText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new LinkTextUrl());

        $violations = $scanner->scan('<div><a href="https://google.com">Google Search Engine</a></div>');
        $this->assertCount(0, $violations);
    }
}
