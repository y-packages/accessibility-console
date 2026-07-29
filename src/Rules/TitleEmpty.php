<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

/**
 * WCAG 2.4.2 — Ensures the <title> element is not empty or whitespace-only.
 *
 * A present but empty <title> is worse than no title at all in some screen
 * readers because the browser may fall back to the filename/URL.
 */
class TitleEmpty extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_2_TITLE_EMPTY'; }
    public function getDescription(): string { return 'The <title> element must not be empty.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'title') {
            return null;
        }

        // Only check <title> inside <head> (not SVG <title>)
        $parent = $element->parentNode;
        if ($parent instanceof DOMElement && strtolower($parent->tagName) !== 'head') {
            return null;
        }

        $text = trim($element->textContent);

        // Skip template expressions
        if (preg_match('/(\{\{|\{%|<\?)/', $text)) {
            return null;
        }

        if ($text === '') {
            return $this->createViolation(
                $element,
                '<title> öğesi boş veya yalnızca boşluk karakterlerinden oluşuyor.',
                'Sayfa içeriğini tanımlayan anlamlı bir <title> metni ekleyin.'
            );
        }

        // Check for generic/placeholder titles
        $genericTitles = [
            'untitled', 'document', 'page', 'no title', 'başlıksız',
            'sayfa', 'doküman', 'test', 'index', 'home page',
        ];

        if (in_array(strtolower($text), $genericTitles, true)) {
            return $this->createViolation(
                $element,
                "Sayfa başlığı çok genel: '{$text}'.",
                'Sayfa içeriğini açıkça tanımlayan benzersiz ve açıklayıcı bir başlık kullanın.'
            );
        }

        return null;
    }
}
