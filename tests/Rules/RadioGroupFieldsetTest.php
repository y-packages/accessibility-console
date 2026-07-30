<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\RadioGroupFieldset;

class RadioGroupFieldsetTest extends TestCase
{
    public function testFlagsRadioGroupWithoutFieldsetLegend(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RadioGroupFieldset());

        $violations = $scanner->scan('<div><input type="radio" name="gender" value="m"><input type="radio" name="gender" value="f"></div>');
        $this->assertGreaterThanOrEqual(1, count($violations));
        $this->assertSame('WCAG_1_3_1_RADIO_GROUP_FIELDSET', $violations[0]->ruleId);
    }

    public function testPassesRadioGroupInFieldsetWithLegend(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RadioGroupFieldset());

        $violations = $scanner->scan('<fieldset><legend>Gender</legend><input type="radio" name="gender" value="m"><input type="radio" name="gender" value="f"></fieldset>');
        $this->assertCount(0, $violations);
    }

    public function testPassesRadioGroupWithRoleRadiogroup(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RadioGroupFieldset());

        $violations = $scanner->scan('<div role="radiogroup" aria-label="Gender"><input type="radio" name="gender" value="m"><input type="radio" name="gender" value="f"></div>');
        $this->assertCount(0, $violations);
    }
}
