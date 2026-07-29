<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaValidAttr extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ARIA_VALID_ATTR'; }
    public function getDescription(): string { return 'ARIA attributes must be valid names.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $validAttributes = [
            'aria-activedescendant', 'aria-atomic', 'aria-autocomplete', 'aria-braillelabel', 
            'aria-brailleroledescription', 'aria-busy', 'aria-checked', 'aria-colcount', 
            'aria-colindex', 'aria-colindextext', 'aria-colspan', 'aria-controls', 
            'aria-current', 'aria-describedby', 'aria-description', 'aria-details', 
            'aria-disabled', 'aria-dropeffect', 'aria-errormessage', 'aria-expanded', 
            'aria-flowto', 'aria-grabbed', 'aria-haspopup', 'aria-hidden', 'aria-invalid', 
            'aria-keyshortcuts', 'aria-label', 'aria-labelledby', 'aria-level', 'aria-live', 
            'aria-modal', 'aria-multiline', 'aria-multiselectable', 'aria-orientation', 
            'aria-owns', 'aria-placeholder', 'aria-posinset', 'aria-pressed', 'aria-readonly', 
            'aria-relevant', 'aria-required', 'aria-roledescription', 'aria-rowcount', 
            'aria-rowindex', 'aria-rowindextext', 'aria-rowspan', 'aria-selected', 
            'aria-setsize', 'aria-sort', 'aria-valuemax', 'aria-valuemin', 'aria-valuenow', 
            'aria-valuetext'
        ];

        $invalidAttrs = [];
        
        foreach ($element->attributes as $attr) {
            /** @var \DOMAttr $attr */
            $name = strtolower($attr->nodeName);
            if (strpos($name, 'aria-') === 0) {
                if (!in_array($name, $validAttributes)) {
                    $invalidAttrs[] = $name;
                }
            }
        }

        if (!empty($invalidAttrs)) {
            $invalidStr = implode(', ', $invalidAttrs);
            return $this->createViolation(
                $element,
                "Geçersiz ARIA nitelikleri bulundu: {$invalidStr}.",
                "Sadece geçerli ARIA niteliklerini kullanın. Geçersiz olanları kaldırın veya düzeltin."
            );
        }

        return null;
    }
}
