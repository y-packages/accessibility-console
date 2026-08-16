<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\RuleEngine;

class StrictRuleSet extends RuleEngine
{
    public function __construct()
    {
        $rules = RuleLevels::getRulesForLevel(5);
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }
}
