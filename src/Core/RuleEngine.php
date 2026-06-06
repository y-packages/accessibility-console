<?php

namespace YakNet\AccessibilityConsole\Core;

class RuleEngine
{
    /** @var array<int, mixed> */
    private array $rules = [];

    public function addRule(mixed $rule): void
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
            if ($rule instanceof AbstractRule) {
                // Document-level rule (Core\AbstractRule style)
                $violations = $rule->check($doc);
                $allViolations = array_merge($allViolations, $violations);
            } elseif ($rule instanceof \YakNet\AccessibilityConsole\Rules\RuleInterface) {
                // Element-level rule (Rules\RuleInterface style)
                $xpath = new \DOMXPath($doc);
                $elements = $xpath->query('//*');
                if ($elements !== false) {
                    foreach ($elements as $element) {
                        if ($element instanceof \DOMElement) {
                            $violation = $rule->check($element);
                            if ($violation) {
                                $allViolations[] = $violation;
                            }
                        }
                    }
                }
            }
        }
        return $allViolations;
    }
}
