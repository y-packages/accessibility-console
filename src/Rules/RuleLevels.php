<?php

namespace YakNet\AccessibilityConsole\Rules;

class RuleLevels
{
    /**
     * Get rule instances for the specified level.
     * Levels are incremental: Level 5 includes all rules from Levels 1, 2, 3, and 4.
     *
     * @param int $level
     * @return array<int, \YakNet\AccessibilityConsole\Rules\RuleInterface|\YakNet\AccessibilityConsole\Core\AbstractRule>
     */
    public static function getRulesForLevel(int $level): array
    {
        $rules = [];
        $files = glob(__DIR__ . '/*.php');
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            $fqcn = 'YakNet\AccessibilityConsole\Rules\\' . $className;

            if (class_exists($fqcn)) {
                $reflection = new \ReflectionClass($fqcn);

                if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                    continue;
                }

                // Check if it is a concrete rule
                $isRule = $reflection->isSubclassOf(\YakNet\AccessibilityConsole\Core\AbstractRule::class) || 
                          $reflection->implementsInterface(\YakNet\AccessibilityConsole\Rules\RuleInterface::class);

                if ($isRule) {
                    // Exclude Turkish custom rules to match standard behavior
                    if (in_array($className, ['EmptyLinkRule', 'HeadingOrderRule', 'ImageAltRule'], true)) {
                        continue;
                    }

                    $instance = $reflection->newInstance();

                    // Check level
                    $ruleLevel = 4; // default
                    if (method_exists($instance, 'getLevel')) {
                        /** @var mixed $levelVal */
                        $levelVal = $instance->getLevel();
                        if (is_int($levelVal)) {
                            $ruleLevel = $levelVal;
                        }
                    }

                    if ($ruleLevel <= $level) {
                        /** @var \YakNet\AccessibilityConsole\Rules\RuleInterface|\YakNet\AccessibilityConsole\Core\AbstractRule $instance */
                        $rules[] = $instance;
                    }
                }
            }
        }

        // Sort rules by class name to ensure deterministic order
        usort($rules, function ($a, $b) {
            return strcmp(get_class($a), get_class($b));
        });

        return $rules;
    }
}
