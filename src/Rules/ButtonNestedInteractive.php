<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ButtonNestedInteractive extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_BUTTON_NESTED_INTERACTIVE';
    }

    public function getDescription(): string
    {
        return '<button> elements must not contain nested interactive controls.';
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
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'button') {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        $xpath = new DOMXPath($doc);
        $nestedInteractives = $xpath->query('.//a[@href] | .//button | .//select | .//textarea | .//input[not(@type="hidden")]', $element);

        if ($nestedInteractives !== false && $nestedInteractives->length > 0) {
            $first = $nestedInteractives->item(0);
            $nestedTag = $first instanceof DOMElement ? strtolower($first->tagName) : 'interactive element';
            return $this->createViolation(
                $element,
                "<button> contains a nested interactive <{$nestedTag}> element.",
                "Remove nested interactive elements from inside the <button> or restructure as sibling elements."
            );
        }

        return null;
    }
}
