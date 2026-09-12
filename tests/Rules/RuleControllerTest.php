<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;
use YakNet\AccessibilityConsole\Rules\RuleController;

class RuleControllerTest extends TestCase
{
    public function testInitializesAndRetrievesRule(): void
    {
        $controller = new RuleController();
        $this->assertGreaterThanOrEqual(130, $controller->count());

        $rule = $controller->getRule('WCAG_2_5_3_LABEL_IN_NAME');
        $this->assertNotNull($rule);
    }

    public function testFilterByStandard(): void
    {
        $controller = new RuleController();
        $aaRules = $controller->filterByStandard(WCAGStandard::AA);

        $this->assertNotEmpty($aaRules);
        foreach ($aaRules as $rule) {
            $this->assertSame(WCAGStandard::AA, $rule->getStandard());
        }
    }

    public function testFilterBySeverity(): void
    {
        $controller = new RuleController();
        $errorRules = $controller->filterBySeverity(Severity::ERROR);

        $this->assertNotEmpty($errorRules);
        foreach ($errorRules as $rule) {
            $this->assertSame(Severity::ERROR, $rule->getSeverity());
        }
    }

    public function testExecuteRuleDirectlyOnDomElement(): void
    {
        $controller = new RuleController();

        $dom = new \DOMDocument();
        $dom->loadHTML('<button aria-label="Cancel">Submit</button>');
        $button = $dom->getElementsByTagName('button')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $button);

        $violation = $controller->executeRule('WCAG_2_5_3_LABEL_IN_NAME', $button);
        $this->assertNotNull($violation);
        $this->assertSame('WCAG_2_5_3_LABEL_IN_NAME', $violation->ruleId);
    }
}
