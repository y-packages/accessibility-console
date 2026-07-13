<?php

namespace YakNet\AccessibilityConsole\Reference;

class WcagReference
{
    /**
     * Look up the WCAG Success Criterion for a given rule ID.
     *
     * @param string $ruleId
     * @return WcagCriterion|null
     */
    public static function lookup(string $ruleId): ?WcagCriterion
    {
        return WcagDatabase::get($ruleId);
    }

    /**
     * Get a list of all WCAG criteria mapped in the console.
     *
     * @return array<string, WcagCriterion>
     */
    public static function getCatalog(): array
    {
        return WcagDatabase::all();
    }
}
