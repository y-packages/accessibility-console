<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class EmptyLink extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_4_LINK_TEXT'; }
    public function getDescription(): string { return 'Links must have discernible text.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a') {
            return null;
        }

        $html = $element->ownerDocument ? $element->ownerDocument->saveHTML($element) : '';
        if ($html !== false && preg_match('/<\?php|\{\{|\{%/i', $html)) {
            return null;
        }

        $text = trim($element->textContent);
        if ($text !== '') {
            return null;
        }

        if ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') {
            return null;
        }

        if ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
            return null;
        }

        // Check for images inside
        $imgs = $element->getElementsByTagName('img');
        foreach ($imgs as $img) {
            if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') {
                return null;
            }
        }

        $svgs = $element->getElementsByTagName('svg');
        foreach ($svgs as $svg) {
            $titles = $svg->getElementsByTagName('title');
            foreach ($titles as $title) {
                if (trim($title->textContent) !== '') {
                    return null;
                }
            }
        }

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

            $spans = $xpath->query('.//span', $element);
            if ($spans !== false) {
                foreach ($spans as $span) {
                    if ($span instanceof DOMElement) {
                        if (trim($span->textContent) !== '') {
                            return null;
                        }
                        if ($span->hasAttribute('aria-label') && trim($span->getAttribute('aria-label')) !== '') {
                            return null;
                        }
                    }
                }
            }
        }

        return $this->createViolation($element, $this->getDescription(), 'Add text content or an aria-label to the link.');
    }
}
