<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HtmlHasLang extends AbstractRule
{
    public function getId(): string { return 'WCAG_3_1_1_LANG'; }
    public function getDescription(): string { return 'The html element must have a lang attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'html') {
            return null;
        }

        if (!$element->hasAttribute('lang') || trim($element->getAttribute('lang')) === '') {
            return $this->createViolation($element, $this->getDescription(), 'Add lang="en" (or your language) to the html tag.');
        }

        return null;
    }
}
