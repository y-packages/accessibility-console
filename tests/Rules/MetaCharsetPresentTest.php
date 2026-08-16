<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\MetaCharsetPresent;

class MetaCharsetPresentTest extends TestCase
{
    public function testFlagsMissingCharsetInHead(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new MetaCharsetPresent());

        $violations = $scanner->scan('<!DOCTYPE html><html><head><title>Test Page</title></head><body><h1>Hello</h1></body></html>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_3_1_1_META_CHARSET', $violations[0]->ruleId);
    }

    public function testPassesWhenMetaCharsetPresent(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new MetaCharsetPresent());

        $violations = $scanner->scan('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Test Page</title></head><body><h1>Hello</h1></body></html>');
        $this->assertCount(0, $violations);
    }
}
