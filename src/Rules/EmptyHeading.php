<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class EmptyHeading extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_EMPTY_HEADING';
    }

    public function getDescription(): string
    {
        return 'Heading elements (h1-h6) must have discernible text content.';
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
        if (!preg_match('/^h([1-6])$/i', $element->tagName)) {
            return null;
        }

        $html = $element->ownerDocument ? $element->ownerDocument->saveHTML($element) : '';
        if ($html !== false && preg_match('/<\?php|\{\{|\{%/i', $html)) {
            return null;
        }

        // 1. Check direct text content
        $text = trim($element->textContent);
        if ($text !== '') {
            return null;
        }

        // 2. Check aria-label
        if ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') {
            return null;
        }

        // 3. Check title attribute
        if ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
            return null;
        }

        // 4. Check aria-labelledby
        if ($element->hasAttribute('aria-labelledby')) {
            $id = trim($element->getAttribute('aria-labelledby'));
            $doc = $element->ownerDocument;
            if ($id !== '' && $doc !== null) {
                $xpath = new \DOMXPath($doc);
                $labels = $xpath->query("//*[@id='$id']");
                if ($labels !== false && $labels->length > 0) {
                    $labelNode = $labels->item(0);
                    if ($labelNode instanceof DOMNode && trim($labelNode->textContent) !== '') {
                        return null;
                    }
                }
            }
        }

        // 5. Check img with alt attribute
        $imgs = $element->getElementsByTagName('img');
        foreach ($imgs as $img) {
            if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') {
                return null;
            }
        }

        // 6. Check svg with title or aria-label
        $svgs = $element->getElementsByTagName('svg');
        foreach ($svgs as $svg) {
            if ($svg->hasAttribute('aria-label') && trim($svg->getAttribute('aria-label')) !== '') {
                return null;
            }
            $titles = $svg->getElementsByTagName('title');
            foreach ($titles as $title) {
                if (trim($title->textContent) !== '') {
                    return null;
                }
            }
        }

        // 7. Check element with role="img" and aria-label
        $doc = $element->ownerDocument;
        if ($doc !== null) {
            $xpath = new \DOMXPath($doc);
            $roleImgs = $xpath->query('.//*[@role="img"]', $element);
            if ($roleImgs !== false) {
                foreach ($roleImgs as $roleImg) {
                    if ($roleImg instanceof DOMElement && $roleImg->hasAttribute('aria-label') && trim($roleImg->getAttribute('aria-label')) !== '') {
                        return null;
                    }
                }
            }
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add descriptive text, an <img> with alt text, or an aria-label attribute to the heading.'
        );
    }
}
