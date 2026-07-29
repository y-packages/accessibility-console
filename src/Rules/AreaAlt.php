<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AreaAlt extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_AreaAlt'; }
    public function getDescription(): string { return 'Checks that <area> elements in image maps have non-empty alt attributes.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'area') {
            return null;
        }
        
        $alt = $element->getAttribute('alt');
        $ariaLabel = $element->getAttribute('aria-label');
        
        if (empty(trim($alt)) && empty(trim($ariaLabel))) {
            return $this->createViolation(
                $element,
                '<area> öğesinde alt veya aria-label eksik.',
                'Provide a non-empty alt or aria-label attribute for the <area> element.'
            );
        }
        
        return null;
    }
}
