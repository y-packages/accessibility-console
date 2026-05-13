<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MetaViewport extends AbstractRule
{
    public function getId(): string { return 'A11Y_VIEWPORT'; }
    public function getDescription(): string { return 'Viewport meta tag should not disable zooming.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::ERROR; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'meta' || $element->getAttribute('name') !== 'viewport') {
            return null;
        }

        $content = $element->getAttribute('content');
        if (str_contains($content, 'user-scalable=no') || str_contains($content, 'maximum-scale=1')) {
            return $this->createViolation($element, $this->getDescription(), 'Remove user-scalable=no or maximum-scale=1 to allow users to zoom.');
        }

        return null;
    }
}
