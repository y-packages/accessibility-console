<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AllCapsLongText extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_1_5_ALL_CAPS_LONG';
    }

    public function getDescription(): string
    {
        return 'Avoid styling long passages of text (> 100 characters) in all-uppercase (text-transform: uppercase), which hinders readability for dyslexic users.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AAA;
    }

    public function getSeverity(): Severity
    {
        return Severity::INFO;
    }

    public function getLevel(): int
    {
        return 5;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['p', 'div', 'span', 'li', 'blockquote', 'section', 'article'], true)) {
            return null;
        }

        $style = $element->getAttribute('style');
        if (!preg_match('/text-transform\s*:\s*uppercase\b/i', $style)) {
            return null;
        }

        $text = trim((string)preg_replace('/\s+/', ' ', $element->textContent));
        if (mb_strlen($text) > 100) {
            return $this->createViolation(
                $element,
                sprintf('Long text block (%d characters) uses text-transform: uppercase, reducing reading speed and comprehension for dyslexic users.', mb_strlen($text)),
                'Use normal sentence case for long text passages and reserve uppercase styling for short labels or headings.'
            );
        }

        return null;
    }
}
