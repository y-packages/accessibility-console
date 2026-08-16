<?php

namespace YakNet\AccessibilityConsole\Tests\Core;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\RuleRegistry;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class RuleRegistryTest extends TestCase
{
    public function testValidateRuleSetHasZeroDuplicates(): void
    {
        $rules = RuleLevels::getRulesForLevel(9);
        $result = RuleRegistry::validate($rules);

        $this->assertTrue($result['valid'], 'Rule validation failed: ' . implode("\n", $result['errors']));
        $this->assertEmpty($result['errors']);
        $this->assertGreaterThanOrEqual(120, $result['count']);
    }

    public function testGetMetadataReturnsAccurateData(): void
    {
        $rules = RuleLevels::getRulesForLevel(1);
        $firstRule = $rules[0];
        $meta = RuleRegistry::getMetadata($firstRule);

        $this->assertArrayHasKey('id', $meta);
        $this->assertArrayHasKey('class', $meta);
        $this->assertArrayHasKey('severity', $meta);
        $this->assertArrayHasKey('standard', $meta);
        $this->assertArrayHasKey('level', $meta);
    }
}
