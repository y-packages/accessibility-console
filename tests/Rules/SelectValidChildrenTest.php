<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\SelectValidChildren;

class SelectValidChildrenTest extends TestCase
{
    public function testFlagsInvalidDivInSelect(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SelectValidChildren());

        $violations = $scanner->scan('<select><div>Option 1</div><option value="1">Option 1</option></select>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_SELECT_CHILDREN', $violations[0]->ruleId);
    }

    public function testPassesValidSelectOptionsAndOptgroups(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new SelectValidChildren());

        $violations = $scanner->scan('<select><optgroup label="Group"><option value="1">Opt 1</option></optgroup></select>');
        $this->assertCount(0, $violations);
    }
}
