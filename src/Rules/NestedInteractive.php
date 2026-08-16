<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class NestedInteractive extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_NESTED_INTERACTIVE';
    }

    public function getDescription(): string
    {
        return 'Interactive elements (<a>, <button>, etc.) must not contain nested interactive controls.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['a', 'button'], true)) {
            return null;
        }
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        $xpath = new DOMXPath($doc);
        $interactives = $xpath->query('.//a[@href] | .//button | .//select | .//textarea | .//input[not(@type="hidden")]', $element);
        
        if ($interactives !== false && $interactives->length > 0) {
            $first = $interactives->item(0);
            $nestedTag = $first instanceof DOMElement ? strtolower($first->tagName) : 'interactive';
            return $this->createViolation(
                $element,
                "Nested interactive control <{$nestedTag}> found inside <{$tag}>.",
                "Ensure interactive controls are not nested inside each other."
            );
        }
        
        return null;
    }
}
