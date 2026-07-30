<?php

namespace YakNet\AccessibilityConsole\Support\Helpers;

use DOMElement;

class DomHelper
{
    /**
     * Check if a DOMElement has a specific parent tag name in its ancestor hierarchy.
     */
    public static function hasAncestorTag(DOMElement $element, string $tagName): bool
    {
        $target = strtolower($tagName);
        $parent = $element->parentNode;
        while ($parent instanceof DOMElement) {
            if (strtolower($parent->tagName) === $target) {
                return true;
            }
            $parent = $parent->parentNode;
        }
        return false;
    }

    /**
     * Get accessible name / visible text from element including aria-label and title.
     */
    public static function getAccessibleName(DOMElement $element): string
    {
        if ($element->hasAttribute('aria-label')) {
            return trim($element->getAttribute('aria-label'));
        }
        if ($element->hasAttribute('title')) {
            return trim($element->getAttribute('title'));
        }
        return trim($element->textContent);
    }
}
