<?php

namespace YakNet\AccessibilityConsole\Rules;

class StandardRuleSet
{
    /**
     * @return array<int, \YakNet\AccessibilityConsole\Rules\RuleInterface|\YakNet\AccessibilityConsole\Core\AbstractRule>
     */
    public static function all(): array
    {
        return RuleLevels::getRulesForLevel(5);
    }
}
