<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LinkTextUrl extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_4_LINK_URL'; }
    public function getDescription(): string { return 'Link text should not be a raw URL.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a') {
            return null;
        }

        $text = trim($element->textContent);
        if (preg_match('/^(https?:\/\/|www\.)[^\s]+$/i', $text)) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Replace the raw URL link text with a descriptive label explaining where the link goes.'
            );
        }

        return null;
    }
}
