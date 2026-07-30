<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ContentInfoParent;

class ContentInfoParentTest extends TestCase
{
    public function testFlagsNestedFooterInSection(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ContentInfoParent());

        $violations = $scanner->scan('<section><footer role="contentinfo"><p>Nested footer</p></footer></section>');
        $this->assertGreaterThanOrEqual(1, count($violations));
        $this->assertSame('WCAG_1_3_1_CONTENTINFO_PARENT', $violations[0]->ruleId);
    }

    public function testPassesTopLevelFooter(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new ContentInfoParent());

        $violations = $scanner->scan('<body><footer role="contentinfo"><p>Top level footer</p></footer></body>');
        $this->assertCount(0, $violations);
    }
}
