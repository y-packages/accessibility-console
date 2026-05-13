<?php

namespace YakNet\AccessibilityConsole\Core;

use YakNet\AccessibilityConsole\Rules\RuleInterface;

class RuleEngine
{
    /** @var RuleInterface[] */
    private array $rules = [];

    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * @return RuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
