<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AriaLabelMisuseOnGeneric;

class AriaLabelMisuseOnGenericTest extends TestCase
{
    public function testFlagsGenericDivWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaLabelMisuseOnGeneric());

        $html = '<div><div aria-label="Card container">Content</div></div>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_4_1_2_ARIA_LABEL_GENERIC', $violations[0]->ruleId);
    }

    public function testPassesDivWithRoleAndAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new AriaLabelMisuseOnGeneric());

        $html = '<div><div role="region" aria-label="Card container">Content</div></div>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }
}
