<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\RedundantEntry;

class RedundantEntryTest extends TestCase
{
    public function testFlagsFormWithShippingAndBillingWithoutSameAsCheckbox(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RedundantEntry());

        $html = '<form>
            <input type="text" name="shipping_address" id="shipping_address">
            <input type="text" name="shipping_city" id="shipping_city">
            <input type="text" name="billing_address" id="billing_address">
            <input type="text" name="billing_city" id="billing_city">
        </form>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_3_3_7_REDUNDANT_ENTRY', $violations[0]->ruleId);
    }

    public function testPassesFormWithSameAsShippingCheckbox(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new RedundantEntry());

        $html = '<form>
            <input type="text" name="shipping_address" id="shipping_address">
            <input type="text" name="shipping_city" id="shipping_city">
            <label><input type="checkbox" name="same_as_shipping"> Billing same as shipping</label>
            <input type="text" name="billing_address" id="billing_address">
            <input type="text" name="billing_city" id="billing_city">
        </form>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }
}
