<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\OptgroupLabel;

class OptgroupLabelTest extends TestCase
{
    public function testFlagsOptgroupWithoutLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new OptgroupLabel());

        $violations = $scanner->scan('<select><optgroup><option>Option 1</option></optgroup></select>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_OPTGROUP_LABEL', $violations[0]->ruleId);
    }

    public function testPassesOptgroupWithLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new OptgroupLabel());

        $violations = $scanner->scan('<select><optgroup label="Fruits"><option>Apple</option></optgroup></select>');
        $this->assertCount(0, $violations);
    }
}
