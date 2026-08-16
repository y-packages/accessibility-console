<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AccesskeyDuplicate;

class AccesskeyDuplicateTest extends TestCase
{
    public function testFlagsDuplicateAccesskeys(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AccesskeyDuplicate());

        $violations = $scanner->scan('<div><a href="/" accesskey="h">Home</a><a href="/help" accesskey="h">Help</a></div>');
        $this->assertCount(2, $violations);
        $this->assertSame('WCAG_2_1_1_ACCESSKEY_DUPLICATE', $violations[0]->ruleId);
    }

    public function testPassesUniqueAccesskeys(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AccesskeyDuplicate());

        $violations = $scanner->scan('<div><a href="/" accesskey="h">Home</a><a href="/help" accesskey="p">Help</a></div>');
        $this->assertCount(0, $violations);
    }
}
