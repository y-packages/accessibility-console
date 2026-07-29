<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class NestedInteractive extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_NestedInteractive'; }
    public function getDescription(): string { return 'Checks for nested interactive elements.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['a', 'button'])) {
            return null;
        }
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $interactives = $xpath->query('.//a | .//button | .//input | .//select | .//textarea', $element);
        
        if ($interactives !== false && $interactives->length > 0) {
            return $this->createViolation(
                $element,
                "İç içe geçmiş etkileşimli öğeler bulundu (<{$tag}> içerisinde).",
                'Ensure interactive controls are not nested.'
            );
        }
        
        return null;
    }
}
