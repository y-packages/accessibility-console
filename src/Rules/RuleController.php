<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class RuleController
{
    /**
     * @var array<string, RuleInterface|AbstractRule>
     */
    private array $rulesRegistry = [];

    /**
     * @param array<int, RuleInterface|AbstractRule>|null $rules
     */
    public function __construct(?array $rules = null)
    {
        $allRules = $rules ?? RuleLevels::getRulesForLevel(9);
        foreach ($allRules as $rule) {
            $id = ($rule instanceof RuleInterface) ? $rule->getId() : (new \ReflectionClass($rule))->getShortName();
            $this->rulesRegistry[$id] = $rule;
        }
    }

    /**
     * Get rule instance by ID.
     */
    public function getRule(string $ruleId): RuleInterface|AbstractRule|null
    {
        return $this->rulesRegistry[$ruleId] ?? null;
    }

    /**
     * Execute a specific rule directly on a DOM element.
     */
    public function executeRule(string $ruleId, DOMElement $element): ?Violation
    {
        $rule = $this->getRule($ruleId);
        if ($rule === null) {
            return null;
        }

        if ($rule instanceof RuleInterface) {
            return $rule->check($element);
        }

        return null;
    }

    /**
     * Filter active rules by WCAG standard (A, AA, AAA).
     *
     * @return array<string, RuleInterface|AbstractRule>
     */
    public function filterByStandard(WCAGStandard $standard): array
    {
        $filtered = [];
        foreach ($this->rulesRegistry as $id => $rule) {
            if (method_exists($rule, 'getStandard') && $rule->getStandard() === $standard) {
                $filtered[$id] = $rule;
            }
        }
        return $filtered;
    }

    /**
     * Filter active rules by severity.
     *
     * @return array<string, RuleInterface|AbstractRule>
     */
    public function filterBySeverity(Severity $severity): array
    {
        $filtered = [];
        foreach ($this->rulesRegistry as $id => $rule) {
            if (method_exists($rule, 'getSeverity') && $rule->getSeverity() === $severity) {
                $filtered[$id] = $rule;
            }
        }
        return $filtered;
    }

    /**
     * Total number of registered rules in this controller.
     */
    public function count(): int
    {
        return count($this->rulesRegistry);
    }
}
