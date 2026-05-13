<?php

namespace YakNet\AccessibilityConsole\Core;

class RuleEngine
{
    /** @var AbstractRule[] */
    private array $rules = [];

    public function addRule(AbstractRule $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * @return Violation[]
     */
    public function run(\DOMDocument $doc): array
    {
        $allViolations = [];
        foreach ($this->rules as $rule) {
            $violations = $rule->check($doc);
            $allViolations = array_merge($allViolations, $violations);
        }
        return $allViolations;
    }
}
