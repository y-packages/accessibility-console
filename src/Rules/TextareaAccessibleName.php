<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TextareaAccessibleName extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_TEXTAREA_LABEL';
    }

    public function getDescription(): string
    {
        return '<textarea> elements must have an accessible name via an associated <label>, aria-label, aria-labelledby, or title.';
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
        if (strtolower($element->tagName) !== 'textarea') {
            return null;
        }

        if (strtolower(trim($element->getAttribute('aria-hidden'))) === 'true' || $element->hasAttribute('hidden')) {
            return null;
        }

        $hasDirectName = ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '')
            || ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '')
            || ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '');

        if ($hasDirectName) {
            return null;
        }

        // Check wrapping <label>
        $parent = $element->parentNode;
        while ($parent instanceof DOMElement) {
            if (strtolower($parent->tagName) === 'label') {
                return null;
            }
            $parent = $parent->parentNode;
        }

        // Check explicit <label for="id">
        $id = trim($element->getAttribute('id'));
        $doc = $element->ownerDocument;
        if ($id !== '' && $doc !== null && preg_match('/^[a-zA-Z0-9_\-:.]+$/', $id)) {
            $xpath = new DOMXPath($doc);
            $labels = $xpath->query('//label[@for="' . $id . '"]');
            if ($labels !== false && $labels->length > 0) {
                return null;
            }
        }

        return $this->createViolation(
            $element,
            '<textarea> element does not have an associated <label>, aria-label, aria-labelledby, or title.',
            'Associate a <label for="message">Message</label> with the <textarea id="message"> element.'
        );
    }
}
