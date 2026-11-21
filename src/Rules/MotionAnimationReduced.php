<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MotionAnimationReduced extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_3_3_REDUCED_MOTION';
    }

    public function getDescription(): string
    {
        return 'Stylesheets defining CSS animations or keyframes should respect the prefers-reduced-motion media query (WCAG 2.3.3).';
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
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'style') {
            return null;
        }

        $css = $element->textContent;
        if (trim($css) === '') {
            return null;
        }

        $hasAnimation = (bool)preg_match('/@keyframes\b|\banimation(?:-name)?\s*:\s*(?!none\b)/i', $css);
        $hasReducedMotion = (bool)preg_match('/prefers-reduced-motion/i', $css);

        if ($hasAnimation && !$hasReducedMotion) {
            return $this->createViolation(
                $element,
                'Stylesheet defines CSS animations without a @media (prefers-reduced-motion: reduce) fallback (WCAG 2.3.3).',
                'Add @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; } }.'
            );
        }

        return null;
    }
}
