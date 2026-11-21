<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TargetSpacingMinimum extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_5_8_TARGET_SPACING';
    }

    public function getDescription(): string
    {
        return 'Interactive controls must not use negative margins that cause overlapping tap targets below WCAG 2.2 spacing thresholds (WCAG 2.5.8).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
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
        $tag = strtolower($element->tagName);
        $role = strtolower($element->getAttribute('role'));

        $isInteractive = in_array($tag, ['a', 'button', 'input', 'select'], true)
            || in_array($role, ['button', 'link', 'tab', 'menuitem', 'checkbox', 'radio'], true);

        if (!$isInteractive) {
            return null;
        }

        $style = $element->getAttribute('style');
        if ($style === '') {
            return null;
        }

        // Detect negative margins on interactive targets (e.g., margin: -4px or margin-left: -10px)
        if (preg_match('/margin(?:-(?:top|right|bottom|left))?\s*:\s*[^;]*-\d+(?:\.\d+)?(?:px|rem|em)/i', $style)) {
            return $this->createViolation(
                $element,
                'Interactive element uses negative inline margin which can cause pointer target overlap (WCAG 2.2 SC 2.5.8).',
                'Avoid negative margins on interactive controls and ensure at least 24x24px target size or adequate spacing between adjacent controls.'
            );
        }

        return null;
    }
}
