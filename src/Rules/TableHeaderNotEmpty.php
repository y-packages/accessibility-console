<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableHeaderNotEmpty extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_TH_NOT_EMPTY';
    }

    public function getDescription(): string
    {
        return '<th> table header cells must have discernible text or an accessible name.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'th') {
            return null;
        }

        $html = $element->ownerDocument ? $element->ownerDocument->saveHTML($element) : '';
        if ($html !== false && preg_match('/<\?php|\{\{|\{%/i', $html)) {
            return null;
        }

        // 1. Text content
        if (trim($element->textContent) !== '') {
            return null;
        }

        // 2. aria-label or title
        if ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') {
            return null;
        }

        if ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
            return null;
        }

        // 3. Img with alt
        $imgs = $element->getElementsByTagName('img');
        foreach ($imgs as $img) {
            if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') {
                return null;
            }
        }

        // 4. Svg with title or aria-label
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

        return $this->createViolation(
            $element,
            "Table header cell <th> is empty and has no accessible name.",
            "Add meaningful text content or an aria-label to describe the column or row represented by this <th>."
        );
    }
}
