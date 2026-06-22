<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class EmptyHeading extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_EMPTY_HEADING'; }
    public function getDescription(): string { return 'Heading elements (h1-h6) must have discernible text content.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (!preg_match('/^h([1-6])$/i', $element->tagName)) {
            return null;
        }

        $html = $element->ownerDocument ? $element->ownerDocument->saveHTML($element) : '';
        if ($html !== false && preg_match('/<\?php|\{\{|\{%/i', $html)) {
            return null;
        }

        $text = trim($element->textContent);
        if ($text === '' && !$element->hasAttribute('aria-label') && !$element->hasAttribute('title')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add descriptive text or an aria-label to the heading element.'
            );
        }

        return null;
    }
}
