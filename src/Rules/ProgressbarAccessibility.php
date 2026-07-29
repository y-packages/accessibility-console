<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ProgressbarAccessibility extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_PROGRESSBAR'; }
    public function getDescription(): string { return 'Progressbar elements must have an accessible name and required aria attributes.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        $role = strtolower($element->getAttribute('role'));
        $tagName = strtolower($element->tagName);
        
        if ($role !== 'progressbar' && $tagName !== 'progress') {
            return null;
        }
        
        $hasName = $element->hasAttribute('aria-label') || $element->hasAttribute('aria-labelledby') || $element->hasAttribute('title');
        
        if (!$hasName) {
            return $this->createViolation(
                $element,
                'İlerleme çubuğunun (progressbar) erişilebilir bir adı eksik. / Progressbar is missing an accessible name.',
                'aria-label, aria-labelledby veya title niteliği ekleyin. / Add aria-label, aria-labelledby, or title attribute.'
            );
        }
        
        if ($role === 'progressbar' && $tagName !== 'progress') {
            $hasValueNow = $element->hasAttribute('aria-valuenow');
            $hasValueMinMax = $element->hasAttribute('aria-valuemin') && $element->hasAttribute('aria-valuemax');
            $hasValueText = $element->hasAttribute('aria-valuetext');
            
            if (!$hasValueNow || (!$hasValueMinMax && !$hasValueText)) {
                return $this->createViolation(
                    $element,
                    'Özel ilerleme çubuğunun (role="progressbar") durum bildiren ARIA nitelikleri eksik. / Custom progressbar is missing state ARIA attributes.',
                    'aria-valuenow ile birlikte aria-valuemin/aria-valuemax veya aria-valuetext niteliklerini ekleyin. / Add aria-valuenow along with aria-valuemin/aria-valuemax or aria-valuetext.'
                );
            }
        }

        return null;
    }
}
