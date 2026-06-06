<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ImgAltText extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_ALT'; }
    public function getDescription(): string { return 'Images must have an alt attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'img') {
            return null;
        }

        if (!$element->hasAttribute('alt')) {
            // Check for decorative roles
            $role = $element->getAttribute('role');
            if ($role === 'presentation' || $role === 'none') {
                return null;
            }

            return $this->createViolation($element, $this->getDescription(), 'Add an alt attribute. Use alt="" for decorative images.');
        }

        return null;
    }
}
