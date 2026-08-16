<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\RoleImgAlt;

class RoleImgAltTest extends TestCase
{
    public function testFlagsRoleImgWithoutAccessibleName(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RoleImgAlt());

        $violations = $scanner->scan('<div><span role="img" class="icon-star"></span></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_1_1_ROLE_IMG', $violations[0]->ruleId);
    }

    public function testPassesRoleImgWithAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RoleImgAlt());

        $violations = $scanner->scan('<div><span role="img" class="icon-star" aria-label="Five Star Rating"></span></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesRoleImgWithAriaHidden(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RoleImgAlt());

        $violations = $scanner->scan('<div><span role="img" class="icon-star" aria-hidden="true"></span></div>');
        $this->assertCount(0, $violations);
    }
}
