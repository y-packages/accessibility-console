<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

/**
 * WCAG 2.4.7 — Ensures that visually hidden elements are not independently focusable.
 *
 * Elements hidden with CSS techniques like `clip`, `clip-path`, or tiny dimensions
 * while remaining in the tab order can confuse keyboard users since focus moves
 * to an invisible element.
 */
class VisuallyHiddenFocusable extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_7_VISUALLY_HIDDEN_FOCUSABLE'; }
    public function getDescription(): string { return 'Visually hidden elements should not be independently focusable unless they become visible on focus.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 5; }

    public function check(DOMElement $element): ?Violation
    {
        $style = $element->getAttribute('style');
        if ($style === '') {
            return null;
        }

        $isVisuallyHidden = false;

        // Detect common visually-hidden CSS patterns
        if (preg_match('/\bclip\s*:\s*rect\s*\(\s*0/i', $style)) {
            $isVisuallyHidden = true;
        } elseif (preg_match('/\bclip-path\s*:\s*inset\s*\(\s*50%\s*\)/i', $style)) {
            $isVisuallyHidden = true;
        } elseif (preg_match('/\bwidth\s*:\s*1px/i', $style) && preg_match('/\bheight\s*:\s*1px/i', $style) && preg_match('/\boverflow\s*:\s*hidden/i', $style)) {
            $isVisuallyHidden = true;
        } elseif (preg_match('/\bposition\s*:\s*absolute/i', $style) && preg_match('/\bleft\s*:\s*-\d{4,}px/i', $style)) {
            $isVisuallyHidden = true;
        }

        if (!$isVisuallyHidden) {
            return null;
        }

        // Check if the element is focusable
        $tag = strtolower($element->tagName);
        $focusableTags = ['a', 'button', 'input', 'select', 'textarea'];
        $isFocusable = in_array($tag, $focusableTags) || $element->hasAttribute('tabindex');

        if ($tag === 'a' && !$element->hasAttribute('href')) {
            $isFocusable = false;
        }

        $tabindex = $element->getAttribute('tabindex');
        if ($tabindex === '-1') {
            $isFocusable = false;
        }

        if (!$isFocusable) {
            return null;
        }

        // Skip if element has :focus styles hinting it becomes visible (we can't detect CSS pseudos in static analysis,
        // but we can check for a common class pattern)
        $class = $element->getAttribute('class');
        if (preg_match('/\b(sr-only-focusable|skip-link|skip-nav|skip-to)\b/i', $class)) {
            return null;
        }

        return $this->createViolation(
            $element,
            'Görsel olarak gizlenmiş bir öğe klavye ile odaklanabilir durumda, bu durum klavye kullanıcılarını yanıltabilir.',
            'Öğeyi tabindex="-1" ile tab sırasından çıkarın veya odaklandığında görünür hale getirin (skip-link deseni).'
        );
    }
}
