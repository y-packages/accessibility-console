<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MeterAccessibility extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_METER_ACCESSIBLE';
    }

    public function getDescription(): string
    {
        return '<meter> elements must have an accessible name and valid values.';
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
        if (strtolower($element->tagName) !== 'meter') {
            return null;
        }

        // 1. Check for accessible name
        $hasName = false;

        if ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') {
            $hasName = true;
        } elseif ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '') {
            $hasName = true;
        } elseif ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
            $hasName = true;
        } elseif ($element->hasAttribute('id')) {
            $id = trim($element->getAttribute('id'));
            $doc = $element->ownerDocument;
            if ($id !== '' && $doc !== null) {
                $xpath = new \DOMXPath($doc);
                $labels = $xpath->query("//label[@for='{$id}']");
                if ($labels !== false && $labels->length > 0) {
                    $hasName = true;
                }
            }
        }

        if (!$hasName) {
            // Check if wrapped in <label>
            $parent = $element->parentNode;
            while ($parent) {
                if ($parent instanceof DOMElement && strtolower($parent->tagName) === 'label') {
                    $hasName = true;
                    break;
                }
                $parent = $parent->parentNode;
            }
        }

        if (!$hasName) {
            return $this->createViolation(
                $element,
                "<meter> element is missing an accessible name.",
                "Add an aria-label, title, or associated <label> element to describe the gauge value."
            );
        }

        return null;
    }
}
