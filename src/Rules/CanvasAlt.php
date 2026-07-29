<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class CanvasAlt extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_CanvasAlt'; }
    public function getDescription(): string { return 'Checks that <canvas> elements have fallback content or aria labels.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'canvas') {
            return null;
        }
        
        $hasAriaLabel = !empty($element->getAttribute('aria-label'));
        $hasAriaLabelledby = !empty($element->getAttribute('aria-labelledby'));
        $hasRoleImgLabel = ($element->getAttribute('role') === 'img') && $hasAriaLabel;
        
        if ($hasAriaLabel || $hasAriaLabelledby || $hasRoleImgLabel) {
            return null;
        }
        
        $content = trim($element->textContent);
        $hasChildElements = false;
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $hasChildElements = true;
                break;
            }
        }
        
        if ($content === '' && !$hasChildElements) {
            return $this->createViolation(
                $element,
                '<canvas> öğesi alternatif içerik veya etiket içermiyor.',
                'Provide fallback content inside the <canvas> tag or use aria-label/aria-labelledby.'
            );
        }
        
        return null;
    }
}
