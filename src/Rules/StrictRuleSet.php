<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\RuleEngine;

class StrictRuleSet extends RuleEngine
{
    public function __construct()
    {
        $this->addRule(new ImageAltRule());
        $this->addRule(new HeadingOrderRule());
        $this->addRule(new EmptyLinkRule());
        // Daha fazla kural buraya eklenebilir
    }
}
