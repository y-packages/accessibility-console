<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FocusAppearance extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_4_13_FOCUS_APPEARANCE';
    }

    public function getDescription(): string
    {
        return 'Focus indicators must have sufficient thickness (at least 2px) and visual clarity (WCAG 2.2 SC 2.4.13).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AAA;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 5;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);

        if ($tag === 'style') {
            $css = $element->textContent;
            if (preg_match('/:focus(?:-visible)?\s*\{[^}]*\boutline(?:-width)?\s*:\s*(?:0(?:\.?\d+)?(?:px|em|rem)?|1px|0\.5px|thin)\b/i', $css)) {
                return $this->createViolation(
                    $element,
                    'CSS :focus rule defines an outline thinner than 2px, failing WCAG 2.2 Focus Appearance (SC 2.4.13).',
                    'Use at least a 2px solid high-contrast focus indicator (e.g., outline: 2px solid #005fcc; outline-offset: 2px;).'
                );
            }
            return null;
        }

        $style = $element->getAttribute('style');
        if ($style === '') {
            return null;
        }

        $isInteractive = in_array($tag, ['a', 'button', 'input', 'select', 'textarea', 'summary'], true)
            || ($element->hasAttribute('tabindex') && (int)$element->getAttribute('tabindex') >= 0);

        if (!$isInteractive) {
            return null;
        }

        if (preg_match('/outline(?:-width)?\s*:\s*(?:1px|0\.5px|thin)\b/i', $style)) {
            return $this->createViolation(
                $element,
                'Interactive element defines an outline thinner than 2px, reducing keyboard focus visibility (WCAG 2.2 SC 2.4.13).',
                'Increase the focus outline thickness to at least 2px with a 3:1 contrast ratio against adjacent colors.'
            );
        }

        return null;
    }
}
