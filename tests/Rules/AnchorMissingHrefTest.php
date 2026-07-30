<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AnchorMissingHref;

class AnchorMissingHrefTest extends TestCase
{
    public function testFlagsAnchorWithoutHref(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AnchorMissingHref());

        $violations = $scanner->scan('<div><a>Empty Link</a></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ANCHOR_HREF', $violations[0]->ruleId);
    }

    public function testPassesAnchorWithValidHref(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AnchorMissingHref());

        $violations = $scanner->scan('<div><a href="https://example.com">Valid Link</a></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesAnchorWithRole(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AnchorMissingHref());

        $violations = $scanner->scan('<div><a role="button">Button Link</a></div>');
        $this->assertCount(0, $violations);
    }
}
