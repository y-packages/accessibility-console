<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class PlaceholderAsLabel extends AbstractRule
{
    public function getId(): string { return 'WCAG_3_3_2_PLACEHOLDER_LABEL'; }
    public function getDescription(): string { return 'Inputs should not use placeholder text as their only label.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }

    public function check(DOMElement $element): ?Violation
    {
        $tags = ['input', 'select', 'textarea'];
        if (!in_array(strtolower($element->tagName), $tags, true)) {
            return null;
        }

        // Skip hidden inputs, buttons, etc.
        $type = strtolower($element->getAttribute('type'));
        if ($type === 'hidden' || $type === 'submit' || $type === 'button' || $type === 'reset') {
            return null;
        }

        // If it doesn't have a placeholder, this rule doesn't apply (FormLabel handles general missing labels)
        if (!$element->hasAttribute('placeholder')) {
            return null;
        }

        // Check if there is an aria-label or aria-labelledby
        if ($element->hasAttribute('aria-label') || $element->hasAttribute('aria-labelledby')) {
            return null;
        }

        // Check for associated label element
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

        // Check if wrapped in label
        $parent = $element->parentNode;
        while ($parent) {
            if ($parent instanceof DOMElement && strtolower($parent->tagName) === 'label') {
                return null;
            }
            $parent = $parent->parentNode;
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add a <label for="..."> or an aria-label attribute. Placeholder text is not a reliable label replacement.'
        );
    }
}
