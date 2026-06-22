<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ObjectAlt extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_OBJECT'; }
    public function getDescription(): string { return 'Object elements must have alternative text or descriptive content.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'object') {
            return null;
        }

        // Object can have title attribute
        if ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
            return null;
        }

        // Or object can contain fallback text content. 
        // We clean up child tags like <param> or <embed> to find actual descriptive text.
        $text = '';
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $text .= $child->textContent;
            } elseif ($child instanceof DOMElement) {
                $tagName = strtolower($child->tagName);
                if ($tagName !== 'param' && $tagName !== 'embed') {
                    $text .= $child->textContent;
                }
            }
        }

        if (trim($text) === '') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add fallback text inside the <object> element or provide a descriptive title attribute.'
            );
        }

        return null;
    }
}
