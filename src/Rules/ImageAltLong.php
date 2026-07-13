<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ImageAltLong extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_ALT_LONG'; }
    public function getDescription(): string { return 'Image alt text should be concise (less than 150 characters).'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'img') {
            return null;
        }

        if (!$element->hasAttribute('alt')) {
            return null;
        }

        $alt = trim($element->getAttribute('alt'));
        if (mb_strlen($alt) > 150) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Shorten the alt text description to be under 150 characters, and move longer explanations to standard page content.'
            );
        }

        return null;
    }
}
