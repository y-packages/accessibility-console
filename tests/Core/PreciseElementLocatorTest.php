<?php

namespace YakNet\AccessibilityConsole\Tests\Core;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Locator\PreciseElementLocator;

class PreciseElementLocatorTest extends TestCase
{
    private PreciseElementLocator $locator;

    protected function setUp(): void
    {
        $this->locator = new PreciseElementLocator();
    }

    public function testLocatesExactMatch(): void
    {
        $content = "<html>\n<body>\n<div class=\"container\">\n<img src=\"logo.png\">\n</div>\n</body>\n</html>";
        $snippet = '<img src="logo.png">';

        $loc = $this->locator->locate($content, $snippet);
        $this->assertSame(4, $loc['line']);
    }

    public function testLocatesAttributeTokenMatch(): void
    {
        $content = "<div>\n<button id=\"save-btn\" class=\"btn btn-primary\">\nSave\n</button>\n</div>";
        // Snippet with different whitespace/attr arrangement
        $snippet = '<button class="btn btn-primary" id="save-btn">';

        $loc = $this->locator->locate($content, $snippet);
        $this->assertSame(2, $loc['line']);
    }
}
