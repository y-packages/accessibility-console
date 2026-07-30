<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaHiddenBody extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ARIA_HIDDEN_BODY'; }
    public function getDescription(): string { return 'The <body> or <html> element must not have aria-hidden="true".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        $tagName = strtolower($element->tagName);
        if ($tagName !== 'body' && $tagName !== 'html') {
            return null;
        }

        if (strtolower($element->getAttribute('aria-hidden')) === 'true') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove aria-hidden="true" from the <body>/<html> tag to prevent hiding the entire document from screen readers.'
            );
        }

        return null;
    }
}
