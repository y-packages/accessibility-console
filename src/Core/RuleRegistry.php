<?php

namespace YakNet\AccessibilityConsole\Core;

use ReflectionClass;
use YakNet\AccessibilityConsole\Rules\RuleInterface;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class RuleRegistry
{
    /**
     * Validate an array of rule instances for ID uniqueness, interfaces, and validity.
     *
     * @param array<int, mixed> $rules
     * @return array{valid: bool, errors: array<int, string>, count: int}
     */
    public static function validate(array $rules): array
    {
        $errors = [];
        $ids = [];

        foreach ($rules as $rule) {
            if (!is_object($rule)) {
                $errors[] = "Non-object rule encountered in rule set.";
                continue;
            }

            $className = get_class($rule);
            $reflection = new ReflectionClass($rule);

            $isDocRule = $rule instanceof AbstractRule;
            $isElementRule = $rule instanceof RuleInterface;

            if (!$isDocRule && !$isElementRule) {
                $errors[] = "Class {$className} does not implement RuleInterface or extend AbstractRule.";
                continue;
            }

            $ruleId = method_exists($rule, 'getId') ? $rule->getId() : $reflection->getShortName();

            if (isset($ids[$ruleId])) {
                $errors[] = "Duplicate Rule ID '{$ruleId}' detected across classes: {$ids[$ruleId]} and {$className}.";
            } else {
                $ids[$ruleId] = $className;
            }

            if (!method_exists($rule, 'getStandard')) {
                $errors[] = "Rule {$className} is missing getStandard() method.";
            }

            if (!method_exists($rule, 'getSeverity')) {
                $errors[] = "Rule {$className} is missing getSeverity() method.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'count' => count($rules),
        ];
    }

    /**
     * Get metadata for a specific rule instance.
     *
     * @param object $rule
     * @return array<string, mixed>
     */
    public static function getMetadata(object $rule): array
    {
        $reflection = new ReflectionClass($rule);
        $id = method_exists($rule, 'getId') ? $rule->getId() : $reflection->getShortName();
        $desc = method_exists($rule, 'getDescription') ? $rule->getDescription() : '';
        $standard = method_exists($rule, 'getStandard') ? $rule->getStandard()->value : 'A';
        $severity = method_exists($rule, 'getSeverity') ? $rule->getSeverity()->value : 'error';
        $level = method_exists($rule, 'getLevel') ? $rule->getLevel() : 4;

        return [
            'id' => $id,
            'class' => $reflection->getName(),
            'shortName' => $reflection->getShortName(),
            'description' => $desc,
            'standard' => $standard,
            'severity' => $severity,
            'level' => $level,
            'type' => ($rule instanceof AbstractRule) ? 'document' : 'element',
        ];
    }
}
