<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class OrientationLockBanned extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_4_ORIENTATION_LOCK';
    }

    public function getDescription(): string
    {
        return 'CSS must not lock display orientation by hiding content or rotating the viewport in portrait/landscape media queries (WCAG 1.3.4).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'style') {
            return null;
        }

        $css = $element->textContent;
        if (!preg_match('/@media[^{]*orientation\s*:\s*(?:portrait|landscape)[^{]*\{/i', $css)) {
            return null;
        }

        // Check if orientation query hides body/main/app or applies 90deg/-90deg rotation lock
        if (preg_match('/@media[^{]*orientation\s*:\s*(?:portrait|landscape)[^{]*\{[\s\S]*?(?:(?:body|main|#app|\.app)\s*\{[^}]*(?:display\s*:\s*none|visibility\s*:\s*hidden)|transform\s*:\s*rotate\(\s*-?90deg\s*\))/i', $css)) {
            return $this->createViolation(
                $element,
                'Stylesheet appears to restrict page display to a single screen orientation (WCAG 1.3.4 Orientation).',
                'Remove orientation locks and use responsive CSS layouts that adapt seamlessly to both portrait and landscape orientations.'
            );
        }

        return null;
    }
}
