<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableCaption extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_TABLE_CAPTION'; }
    public function getDescription(): string { return 'Data tables should have a caption element or appropriate ARIA description labels.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'table') {
            return null;
        }

        // Data tables usually contain headers (<th> elements). If it is a layout table, this rule is not mandatory.
        $thElements = $element->getElementsByTagName('th');
        if ($thElements->length === 0) {
            return null;
        }

        // Check if there is a <caption> direct child
        $hasCaption = false;
        $captionElements = $element->getElementsByTagName('caption');
        foreach ($captionElements as $caption) {
            if ($caption->parentNode === $element && trim($caption->textContent) !== '') {
                $hasCaption = true;
                break;
            }
        }

        if ($hasCaption) {
            return null;
        }

        // Check for aria-label or aria-labelledby or summary attributes
        $hasAriaLabel = ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') ||
                         ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '');
        
        $hasSummary = $element->hasAttribute('summary') && trim($element->getAttribute('summary')) !== '';

        if ($hasAriaLabel || $hasSummary) {
            return null;
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add a <caption> element directly inside the <table> or use the aria-label attribute to describe the table.'
        );
    }
}
