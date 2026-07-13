<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SvgAltText extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_SVG'; }
    public function getDescription(): string { return 'SVG elements must have alternative text or be hidden from screen readers.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'svg') {
            return null;
        }

        if ($element->getAttribute('aria-hidden') === 'true') {
            return null;
        }

        if ($element->hasAttribute('aria-label') || $element->hasAttribute('aria-labelledby')) {
            return null;
        }

        $titles = $element->getElementsByTagName('title');
        if ($titles->length > 0) {
            $titleNode = $titles->item(0);
            if ($titleNode !== null) {
                $titleText = trim($titleNode->textContent);
                if ($titleText !== '') {
                    return null;
                }
            }
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add an aria-label, aria-labelledby, a <title> element inside the SVG, or set aria-hidden="true" if it is decorative.'
        );
    }
}
