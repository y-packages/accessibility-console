<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class EmptyLabel extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_EMPTY_LABEL'; }
    public function getDescription(): string { return 'Label elements must have discernible text content.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'label') {
            return null;
        }

        $html = $element->ownerDocument ? $element->ownerDocument->saveHTML($element) : '';
        if ($html !== false && preg_match('/<\?php|\{\{|\{%/i', $html)) {
            return null;
        }

        $text = trim($element->textContent);
        if ($text === '') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add text content to the label element to describe its associated input.'
            );
        }

        return null;
    }
}
