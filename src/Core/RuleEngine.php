<?php

namespace YakNet\AccessibilityConsole\Core;

use DOMDocument;
use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Rules\RuleExecutionStats;
use YakNet\AccessibilityConsole\Rules\RuleInterface;

class RuleEngine
{
    /** @var array<int, mixed> */
    private array $rules = [];

    private ?RuleExecutionStats $stats = null;

    public function setStats(?RuleExecutionStats $stats): void
    {
        $this->stats = $stats;
    }

    public function getStats(): ?RuleExecutionStats
    {
        return $this->stats;
    }

    public function addRule(mixed $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * @return array<int, mixed>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Run all registered rules against the DOMDocument using single-pass DOM traversal.
     *
     * @param DOMDocument $doc
     * @return Violation[]
     */
    public function run(DOMDocument $doc): array
    {
        $allViolations = [];
        $docRules = [];
        $elementRules = [];

        foreach ($this->rules as $rule) {
            if ($rule instanceof AbstractRule) {
                $docRules[] = $rule;
            } elseif ($rule instanceof RuleInterface) {
                $elementRules[] = $rule;
            }
        }

        // 1. Run Document-level rules
        foreach ($docRules as $rule) {
            $start = $this->stats !== null ? hrtime(true) : 0;
            $violations = $rule->check($doc);
            if ($this->stats !== null) {
                $elapsedMs = (hrtime(true) - $start) / 1_000_000;
                $this->stats->record($rule->getId(), $elapsedMs, !empty($violations));
            }
            $allViolations = array_merge($allViolations, $violations);
        }

        // 2. Run Element-level rules in a single DOM pass
        if (!empty($elementRules)) {
            $xpath = new DOMXPath($doc);
            $elements = $xpath->query('//*');

            if ($elements !== false) {
                foreach ($elements as $element) {
                    if (!$element instanceof DOMElement) {
                        continue;
                    }

                    foreach ($elementRules as $rule) {
                        $start = $this->stats !== null ? hrtime(true) : 0;
                        $violation = $rule->check($element);
                        if ($this->stats !== null) {
                            $elapsedMs = (hrtime(true) - $start) / 1_000_000;
                            $this->stats->record($rule->getId(), $elapsedMs, $violation !== null);
                        }
                        if ($violation !== null) {
                            $allViolations[] = $violation;
                        }
                    }
                }
            }
        }

        return $allViolations;
    }
}
