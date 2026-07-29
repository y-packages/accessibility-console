<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class EmbedAccessibility extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_EmbedAccessibility'; }
    public function getDescription(): string { return 'Checks that <embed> elements have aria-label, aria-labelledby, or title attribute for accessible name.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'embed') {
            return null;
        }
        
        $ariaLabel = $element->getAttribute('aria-label');
        $ariaLabelledby = $element->getAttribute('aria-labelledby');
        $title = $element->getAttribute('title');
        
        if (empty(trim($ariaLabel)) && empty(trim($ariaLabelledby)) && empty(trim($title))) {
            return $this->createViolation(
                $element,
                '<embed> öğesinde erişilebilir isim eksik.',
                'Provide an aria-label, aria-labelledby, or title attribute for the <embed> element.'
            );
        }
        
        return null;
    }
}
