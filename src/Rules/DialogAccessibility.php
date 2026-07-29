<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class DialogAccessibility extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_DIALOG_ACCESSIBILITY'; }
    public function getDescription(): string { return 'Dialog elements must have an accessible name.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        $role = strtolower($element->getAttribute('role'));
        $tagName = strtolower($element->tagName);
        
        if ($role !== 'dialog' && $role !== 'alertdialog' && $tagName !== 'dialog') {
            return null;
        }
        
        $ariaLabel = trim($element->getAttribute('aria-label'));
        $ariaLabelledby = trim($element->getAttribute('aria-labelledby'));
        $title = trim($element->getAttribute('title'));
        
        if (!empty($ariaLabel) || !empty($ariaLabelledby) || !empty($title)) {
            return null;
        }

        return $this->createViolation(
            $element,
            'Dialog bileşeninin erişilebilir bir adı (accessible name) eksik. / Dialog component is missing an accessible name.',
            'Dialog elementine aria-label veya aria-labelledby niteliği ekleyin. / Add aria-label or aria-labelledby to the dialog element.'
        );
    }
}
