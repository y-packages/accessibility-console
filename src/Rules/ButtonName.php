<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ButtonName extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_BUTTON'; }
    public function getDescription(): string { return 'Buttons must have discernible text.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'button') {
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

        if ($element->hasAttribute('aria-labelledby')) {
            $id = $element->getAttribute('aria-labelledby');
            $doc = $element->ownerDocument;
            if ($doc !== null) {
                $xpath = new \DOMXPath($doc);
                $labels = $xpath->query("//*[@id='$id']");
                if ($labels !== false && $labels->length > 0) {
                    $labelNode = $labels->item(0);
                    if ($labelNode instanceof \DOMNode && trim($labelNode->textContent) !== '') {
                        return null;
                    }
                }
            }
        }

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
        }

        return $this->createViolation($element, $this->getDescription(), 'Add text or an aria-label to the button.');
    }
}
