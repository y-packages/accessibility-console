<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\BannedSelectors;

class BannedSelectorsTest extends TestCase
{
    public function testFlagsBannedCssSelectorTags(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new BannedSelectors());

        $violations = $scanner->scan('<div><center>Obsolete tag</center><div align="center">Deprecated align</div></div>');
        $this->assertCount(2, $violations);
        
        $ruleIds = array_map(fn($v) => $v->ruleId, $violations);
        $this->assertContains('BannedSelectors', $ruleIds);
    }

    public function testPassesSemanticHtml(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new BannedSelectors());

        $violations = $scanner->scan('<div><span style="text-align: center;">Semantic structure</span></div>');
        $this->assertCount(0, $violations);
    }
}
