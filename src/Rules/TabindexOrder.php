<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TabindexOrder extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_3_TABINDEX'; }
    public function getDescription(): string { return 'Avoid positive tabindex values to maintain natural keyboard focus order.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }

    public function check(DOMElement $element): ?Violation
    {
        if ($element->hasAttribute('tabindex')) {
            $tabindex = $element->getAttribute('tabindex');
            if (is_numeric($tabindex)) {
                $val = (int)$tabindex;
                if ($val > 0) {
                    return $this->createViolation(
                        $element,
                        "Avoid positive tabindex values (tabindex=\"{$val}\") to preserve the natural tab order.",
                        "Remove the positive tabindex or change it to \"0\" or \"-1\"."
                    );
                }
            }
        }
        return null;
    }
}
