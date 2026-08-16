<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaCurrentValid extends AbstractRule
{
    /** @var array<int, string> */
    private static array $validValues = [
        'page', 'step', 'location', 'date', 'time', 'true', 'false'
    ];

    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_CURRENT';
    }

    public function getDescription(): string
    {
        return 'The aria-current attribute must have a valid token value.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('aria-current')) {
            return null;
        }

        $value = strtolower(trim($element->getAttribute('aria-current')));

        if (!in_array($value, self::$validValues, true)) {
            $validList = implode(', ', self::$validValues);
            return $this->createViolation(
                $element,
                "Invalid aria-current value \"{$value}\". Valid values are: {$validList}.",
                "Set aria-current to one of the valid token values: page, step, location, date, time, true, or false."
            );
        }

        return null;
    }
}
