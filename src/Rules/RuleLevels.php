<?php

namespace YakNet\AccessibilityConsole\Rules;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use YakNet\AccessibilityConsole\Core\AbstractRule;

class RuleLevels
{
    /**
     * Get rule instances for the specified level.
     * Levels are incremental: Level 5 includes all rules from Levels 1, 2, 3, and 4.
     * Recursively scans src/Rules and all subdirectories for rule classes.
     *
     * @param int $level
     * @return array<int, \YakNet\AccessibilityConsole\Rules\RuleInterface|\YakNet\AccessibilityConsole\Core\AbstractRule>
     */
    public static function getRulesForLevel(int $level): array
    {
        $rules = [];
        $dir = __DIR__;

        if (!is_dir($dir)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $realPath = $file->getRealPath();
            $relativePath = str_replace([$dir . DIRECTORY_SEPARATOR, '.php'], ['', ''], $realPath);
            $subNamespace = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
            $fqcn = 'YakNet\AccessibilityConsole\Rules\\' . $subNamespace;

            if (!class_exists($fqcn)) {
                // Fallback to flat namespace if class is in root Rules namespace
                $flatClassName = $file->getBasename('.php');
                $fqcn = 'YakNet\AccessibilityConsole\Rules\\' . $flatClassName;
            }

            if (class_exists($fqcn)) {
                $reflection = new ReflectionClass($fqcn);

                if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                    continue;
                }

                // Check if it is a concrete rule
                $isRule = $reflection->isSubclassOf(AbstractRule::class) || 
                          $reflection->implementsInterface(RuleInterface::class);

                if ($isRule) {
                    $className = $file->getBasename('.php');
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
