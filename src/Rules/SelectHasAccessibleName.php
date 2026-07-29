<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SelectHasAccessibleName extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_SELECT_ACCESSIBLE_NAME'; }
    public function getDescription(): string { return 'Select elements must have an accessible name.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'select') {
            return null;
        }

        // Check aria-label
        if ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') {
            return null;
        }

        // Check aria-labelledby
        if ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '') {
            return null;
        }

        // Check title
        if ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
            return null;
        }

        // Check if there is an associated <label for="id">
        $id = $element->getAttribute('id');
        if (!empty($id)) {
            $doc = $element->ownerDocument;
            if ($doc === null) {
                return null;
            }
            $xpath = new \DOMXPath($doc);
            // escape quotes in id
            $safeId = addslashes($id);
            $labels = $xpath->query("//label[@for='{$safeId}']");
            if ($labels !== false && $labels->length > 0) {
                return null;
            }
        }

        // Check if wrapped in a <label>
        $parent = $element->parentNode;
        while ($parent !== null && $parent instanceof DOMElement) {
            if (strtolower($parent->tagName) === 'label') {
                return null;
            }
            $parent = $parent->parentNode;
        }

        return $this->createViolation(
            $element,
            "<select> elemanının erişilebilir bir adı (label) yok.",
            "<select> elemanına erişilebilir bir ad sağlamak için bir <label> ilişkilendirin veya aria-label niteliği ekleyin."
        );
    }
}
