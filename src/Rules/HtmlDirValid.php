<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HtmlDirValid extends AbstractRule
{
    /** @var array<int, string> */
    private static array $validDirValues = ['ltr', 'rtl', 'auto'];

    public function getId(): string
    {
        return 'WCAG_1_3_2_HTML_DIR';
    }

    public function getDescription(): string
    {
        return 'The dir attribute must have a valid value (ltr, rtl, or auto).';
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
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('dir')) {
            return null;
        }

        $dir = strtolower(trim($element->getAttribute('dir')));

        if (!in_array($dir, self::$validDirValues, true)) {
            return $this->createViolation(
                $element,
                "Invalid dir attribute value \"{$dir}\". Valid values are 'ltr', 'rtl', or 'auto'.",
                "Set dir to \"ltr\", \"rtl\", or \"auto\" for proper bidirectional text handling."
            );
        }

        return null;
    }
}
