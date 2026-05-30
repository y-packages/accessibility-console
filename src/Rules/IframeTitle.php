<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class IframeTitle extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_IFRAME_TITLE'; }
    public function getDescription(): string { return 'Iframe elements must have a non-empty title attribute to describe their content.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'iframe') {
            return null;
        }

        if (!$element->hasAttribute('title') || trim($element->getAttribute('title')) === '') {
            return $this->createViolation(
                $element,
                "Iframe element is missing a descriptive title attribute.",
                "Add a descriptive title attribute, e.g., title=\"Embedded Map\" or title=\"Video Player\"."
            );
        }

        return null;
    }
}
