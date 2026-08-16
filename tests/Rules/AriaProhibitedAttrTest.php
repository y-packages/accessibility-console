<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaProhibitedAttr;

class AriaProhibitedAttrTest extends TestCase
{
    public function testFlagsAriaLabelOnPresentationRole(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaProhibitedAttr());

        $violations = $scanner->scan('<div><div role="presentation" aria-label="Decorative block">Content</div></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_PROHIBITED_ATTR', $violations[0]->ruleId);
    }

    public function testFlagsAriaLabelledbyOnNoneRole(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaProhibitedAttr());

        $violations = $scanner->scan('<div><div role="none" aria-labelledby="some_id">Content</div></div>');
        $this->assertCount(1, $violations);
    }

    public function testPassesValidRolesWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaProhibitedAttr());

        $violations = $scanner->scan('<div><div role="region" aria-label="Main Section">Content</div></div>');
        $this->assertCount(0, $violations);
    }
}
