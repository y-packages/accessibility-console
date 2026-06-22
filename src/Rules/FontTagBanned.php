<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FontTagBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_FONT_TAG_BANNED'; }
    public function getDescription(): string { return 'The font tag is obsolete and should not be used. Use CSS for styling instead.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) === 'font') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the <font> tag and style the text using CSS instead.'
            );
        }

        return null;
    }
}
