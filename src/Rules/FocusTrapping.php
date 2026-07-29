<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FocusTrapping extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_1_2_FOCUS_TRAP'; }
    public function getDescription(): string { return 'Detects potential keyboard traps via inline event handlers.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 5; }

    public function check(DOMElement $element): ?Violation
    {
        $onkeydown = $element->getAttribute('onkeydown');
        $onkeypress = $element->getAttribute('onkeypress');
        
        $handlers = $onkeydown . ' ' . $onkeypress;
        
        if (empty(trim($handlers))) {
            return null;
        }
        
        if (strpos($handlers, 'preventDefault') !== false || strpos($handlers, 'return false') !== false) {
            return $this->createViolation(
                $element,
                'Klavye odağını hapseden (keyboard trap) potansiyel olay işleyicisi tespit edildi. / Potential keyboard trap detected in inline event handlers.',
                'Kullanıcıların Esc veya Tab tuşları ile bu alandan çıkabilmesini sağlayın. / Ensure users can exit this component using Esc or Tab keys.'
            );
        }

        return null;
    }
}
