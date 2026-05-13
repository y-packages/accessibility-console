<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HeadingOrder extends AbstractRule
{
    private static int $lastLevel = 0;

    public function getId(): string { return 'WCAG_1_3_1_HEADING'; }
    public function getDescription(): string { return 'Headings should follow a logical nesting order.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }

    public function check(DOMElement $element): ?Violation
    {
        if (!preg_match('/^h([1-6])$/i', $element->tagName, $matches)) {
            return null;
        }

        $currentLevel = (int)$matches[1];
        
        // This rule is a bit complex in a per-element check without global state,
        // but we can at least check if an h2 comes before an h1 in some cases or jumps.
        // For simplicity in this engine, we warn if it's not starting with H1 or jumps too many levels.
        
        if ($currentLevel > self::$lastLevel + 1 && self::$lastLevel !== 0) {
            $violation = $this->createViolation($element, "Heading level h$currentLevel jumps from h" . self::$lastLevel, "Ensure headings follow a logical order (h1, then h2, etc.)");
            self::$lastLevel = $currentLevel;
            return $violation;
        }

        self::$lastLevel = $currentLevel;
        return null;
    }
}
