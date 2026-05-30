<?php

namespace YakNet\AccessibilityConsole\Core;

class RuleEngine
{
    /** @var array */
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
            if (method_exists($rule, 'check')) {
                // Determine if it's a document-level rule or element-level rule
                $reflection = new \ReflectionMethod($rule, 'check');
                $params = $reflection->getParameters();
                
                $type = $params[0]->getType();
                if (isset($params[0]) && $type instanceof \ReflectionNamedType && $type->getName() === 'DOMDocument') {
                    // Document-level rule (Core\AbstractRule style)
                    $violations = $rule->check($doc);
                    $allViolations = array_merge($allViolations, $violations);
                } else {
                    // Element-level rule (Rules\RuleInterface style)
                    // We need to iterate over all elements
                    $xpath = new \DOMXPath($doc);
                    $elements = $xpath->query('//*');
                    foreach ($elements as $element) {
                        $violation = $rule->check($element);
                        if ($violation) {
                            $allViolations[] = $violation;
                        }
                    }
                }
            }
        }
        return $allViolations;
    }
}
