<?php

namespace YakNet\AccessibilityConsole\Tests\AutoFix;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\AutoFix\HtmlRepairEngine;

class HtmlRepairEngineTest extends TestCase
{
    public function testRepairsMissingLangAndSvg(): void
    {
        $html = '<html><body><svg class="icon"></svg><img src="test.png"></body></html>';
        $result = HtmlRepairEngine::autoRepair($html);

        $this->assertStringContainsString('<html lang="tr"', $result['repaired_html']);
        $this->assertStringContainsString('<svg aria-hidden="true"', $result['repaired_html']);
        $this->assertStringContainsString('<img alt=""', $result['repaired_html']);
        $this->assertCount(3, $result['fixes_applied']);
    }
}
