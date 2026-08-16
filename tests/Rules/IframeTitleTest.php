<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\IframeTitle;

class IframeTitleTest extends TestCase
{
    public function testFlagsIframeWithoutTitle(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new IframeTitle());

        $violations = $scanner->scan('<div><iframe src="map.html"></iframe></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_IFRAME_TITLE', $violations[0]->ruleId);
    }

    public function testPassesIframeWithTitle(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new IframeTitle());

        $violations = $scanner->scan('<div><iframe src="map.html" title="Office Location Map"></iframe></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesIframeWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new IframeTitle());

        $violations = $scanner->scan('<div><iframe src="map.html" aria-label="Interactive Map"></iframe></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesAriaHiddenIframe(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new IframeTitle());

        $violations = $scanner->scan('<div><iframe src="tracker.html" aria-hidden="true"></iframe></div>');
        $this->assertCount(0, $violations);
    }
}
