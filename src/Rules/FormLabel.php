<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FormLabel extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_LABEL';
    }

    public function getDescription(): string
    {
        return 'Form inputs must have an associated label.';
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
        $tags = ['input', 'select', 'textarea'];
        if (!in_array(strtolower($element->tagName), $tags, true)) {
            return null;
        }

        // Skip hidden inputs and buttons
        $type = strtolower($element->getAttribute('type'));
        if ($type === 'hidden' || $type === 'submit' || $type === 'button' || $type === 'reset' || $type === 'image') {
            return null;
        }

        // 1. Check label for="id"
        if ($element->hasAttribute('id')) {
            $id = trim($element->getAttribute('id'));
            $doc = $element->ownerDocument;
            if ($id !== '' && $doc !== null) {
                $xpath = new \DOMXPath($doc);
                $labels = $xpath->query("//label[@for='$id']");
                if ($labels !== false && $labels->length > 0) {
                    return null;
                }
            }
        }

        // 2. Check aria-label
        if ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') {
            return null;
        }

        // 3. Check aria-labelledby
        if ($element->hasAttribute('aria-labelledby')) {
            $ariaLabelledby = trim($element->getAttribute('aria-labelledby'));
            if ($ariaLabelledby !== '') {
                $doc = $element->ownerDocument;
                if ($doc !== null) {
                    $xpath = new \DOMXPath($doc);
                    $ids = array_filter(explode(' ', $ariaLabelledby));
                    foreach ($ids as $targetId) {
                        $targets = $xpath->query("//*[@id='$targetId']");
                        if ($targets !== false && $targets->length > 0) {
                            return null;
                        }
                    }
                }
            }
        }

        // 4. Check title attribute
        if ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
            return null;
        }

        // 5. Check if wrapped in label
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
            'Add a <label for="..."> or an aria-label attribute to associate a label with this input.'
        );
    }
}
