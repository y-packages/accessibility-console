<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InputImageAlt extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_INPUT_IMAGE_ALT'; }
    public function getDescription(): string { return '<input type="image"> elements must have an alternative text.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        if (strtolower($element->getAttribute('type')) !== 'image') {
            return null;
        }

        $hasAlt = $element->hasAttribute('alt') && trim($element->getAttribute('alt')) !== '';
        $hasAriaLabel = $element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '';
        $hasAriaLabelledBy = $element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '';
        $hasTitle = $element->hasAttribute('title') && trim($element->getAttribute('title')) !== '';

        if (!$hasAlt && !$hasAriaLabel && !$hasAriaLabelledBy && !$hasTitle) {
            return $this->createViolation(
                $element,
                "<input type=\"image\"> elemanının alternatif metni (alt) bulunmuyor.",
                "Görsel butonlar (<input type=\"image\">) için alt, aria-label veya title niteliklerinden birini kullanarak amacını açıklayın."
            );
        }

        return null;
    }
}
