<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LanguageSubtag extends AbstractRule
{
    public function getId(): string { return 'WCAG_3_1_2_LANG'; }
    public function getDescription(): string { return 'Language attributes (lang) must use a valid language code format.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('lang')) {
            return null;
        }

        $lang = trim($element->getAttribute('lang'));
        
        // Simple IETF language tag format check (e.g. "en", "tr", "en-US", "zh-Hant")
        $isValid = preg_match('/^[a-zA-Z]{2,3}(-[a-zA-Z0-9]{2,8})*$/', $lang);

        if (!$isValid || $lang === '') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Use a valid IETF language code format for the lang attribute (e.g., "tr", "en", "en-US").'
            );
        }

        return null;
    }
}
