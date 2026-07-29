<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaValidAttrValue extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ARIA_VALID_ATTR_VALUE'; }
    public function getDescription(): string { return 'ARIA attributes must contain valid values.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        $validationMap = [
            'aria-hidden' => ['true', 'false'],
            'aria-live' => ['off', 'polite', 'assertive'],
            'aria-expanded' => ['true', 'false', 'undefined'],
            'aria-pressed' => ['true', 'false', 'mixed', 'undefined'],
            'aria-checked' => ['true', 'false', 'mixed', 'undefined'],
            'aria-current' => ['page', 'step', 'location', 'date', 'time', 'true', 'false'],
            'aria-sort' => ['ascending', 'descending', 'none', 'other'],
            'aria-orientation' => ['horizontal', 'vertical', 'undefined'],
            'aria-haspopup' => ['true', 'false', 'menu', 'listbox', 'tree', 'grid', 'dialog'],
            'aria-invalid' => ['grammar', 'false', 'spelling', 'true'],
            'aria-autocomplete' => ['inline', 'list', 'both', 'none'],
            'aria-dropeffect' => ['copy', 'execute', 'link', 'move', 'none', 'popup']
        ];

        for ($i = 0; $i < $element->attributes->length; $i++) {
            $attr = $element->attributes->item($i);
            if (!$attr instanceof \DOMAttr) {
                continue;
            }
            $name = strtolower($attr->nodeName);
            if (isset($validationMap[$name])) {
                $value = strtolower(trim($attr->nodeValue ?? ''));
                // Handle templates like {{ value }} or template syntax
                if (preg_match('/^(\{\{|\{%).*(\}\}|\%\})$/', $value) || preg_match('/^' . preg_quote('<' . '?', '/') . '.*' . preg_quote('?' . '>', '/') . '$/', $value)) {
                    continue;
                }
                
                if (!in_array($value, $validationMap[$name])) {
                    $validStr = implode(', ', $validationMap[$name]);
                    return $this->createViolation(
                        $element,
                        "{$name} niteliği geçersiz bir değere sahip: '{$value}'.",
                        "{$name} niteliğinin değerini geçerli olanlardan biriyle değiştirin: {$validStr}."
                    );
                }
            } elseif ($name === 'aria-relevant') {
                $values = explode(' ', strtolower(trim($attr->nodeValue ?? '')));
                $validRelevant = ['additions', 'removals', 'text', 'all'];
                $rawValue = $attr->nodeValue ?? '';
                $isTemplate = preg_match('/^(\{\{|\{%).*(\}\}|\%\})$/', trim($rawValue)) || preg_match('/^' . preg_quote('<' . '?', '/') . '.*' . preg_quote('?' . '>', '/') . '$/', trim($rawValue));
                
                if (!$isTemplate) {
                    foreach ($values as $val) {
                        if (!empty($val) && !in_array($val, $validRelevant)) {
                            return $this->createViolation(
                                $element,
                                "aria-relevant niteliği geçersiz bir değer içeriyor: '{$val}'.",
                                "aria-relevant değerlerini sadece additions, removals, text, all kelimelerinden oluşacak şekilde ayarlayın."
                            );
                        }
                    }
                }
            }
        }

        return null;
    }
}
