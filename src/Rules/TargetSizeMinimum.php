<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TargetSizeMinimum extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_5_8_TARGET_SIZE';
    }

    public function getDescription(): string
    {
        return 'Interactive elements must have a minimum target size of at least 24x24 CSS pixels (WCAG 2.2).';
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
        $tagName = strtolower($element->tagName);
        $isInteractive = false;

        if ($tagName === 'a' && $element->hasAttribute('href')) {
            $isInteractive = true;
        } elseif (in_array($tagName, ['button', 'select', 'textarea'], true)) {
            $isInteractive = true;
        } elseif ($tagName === 'input') {
            $type = strtolower($element->getAttribute('type'));
            if ($type !== 'hidden') {
                $isInteractive = true;
            }
        } elseif ($element->hasAttribute('role')) {
            $role = strtolower(trim($element->getAttribute('role')));
            if (in_array($role, ['button', 'link', 'checkbox', 'radio', 'tab', 'menuitem'], true)) {
                $isInteractive = true;
            }
        }

        if (!$isInteractive) {
            return null;
        }

        $style = $element->getAttribute('style');
        if (trim($style) === '') {
            return null;
        }

        $width = $this->parsePixelDimension($style, 'width');
        $height = $this->parsePixelDimension($style, 'height');

        $tooSmallWidth = ($width !== null && $width < 24.0);
        $tooSmallHeight = ($height !== null && $height < 24.0);

        if ($tooSmallWidth || $tooSmallHeight) {
            $wStr = $width !== null ? "{$width}px" : "auto";
            $hStr = $height !== null ? "{$height}px" : "auto";
            return $this->createViolation(
                $element,
                "Interactive element target size ({$wStr} x {$hStr}) is smaller than the required 24x24px minimum (WCAG 2.2 SC 2.5.8).",
                "Ensure interactive targets have a minimum width and height of at least 24x24px, or add adequate spacing/padding around them."
            );
        }

        return null;
    }

    private function parsePixelDimension(string $style, string $prop): ?float
    {
        if (preg_match('/(?:^|;)\s*' . preg_quote($prop, '/') . '\s*:\s*([0-9.]+)\s*px/i', $style, $matches)) {
            return (float)$matches[1];
        }
        return null;
    }
}
