<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FormLabel extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LABEL'; }
    public function getDescription(): string { return 'Form inputs must have an associated label.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        $tags = ['input', 'select', 'textarea'];
        if (!in_array(strtolower($element->tagName), $tags)) {
            return null;
        }

        // Skip hidden inputs and buttons
        $type = strtolower($element->getAttribute('type'));
        if ($type === 'hidden' || $type === 'submit' || $type === 'button' || $type === 'reset') {
            return null;
        }

        if ($element->hasAttribute('id')) {
            $id = $element->getAttribute('id');
            $doc = $element->ownerDocument;
            if ($doc !== null) {
                $xpath = new \DOMXPath($doc);
                $labels = $xpath->query("//label[@for='$id']");
                if ($labels !== false && $labels->length > 0) {
                    return null;
                }
            }
        }

        // Check for aria-label, aria-labelledby, or title
        if ($element->hasAttribute('aria-label') || $element->hasAttribute('aria-labelledby') || $element->hasAttribute('title')) {
            return null;
        }

        // Check if wrapped in label
        $parent = $element->parentNode;
        while ($parent) {
            if ($parent instanceof DOMElement && strtolower($parent->tagName) === 'label') {
                return null;
            }
            $parent = $parent->parentNode;
        }

        return $this->createViolation($element, $this->getDescription(), 'Add a <label for="..."> or an aria-label attribute.');
    }
}
