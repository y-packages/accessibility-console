<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Violation;

interface RuleInterface
{
    public function getId(): string;
    public function check(DOMElement $element): ?Violation;
}
