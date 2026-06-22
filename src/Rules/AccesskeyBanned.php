<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AccesskeyBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_1_1_ACCESSKEY'; }
    public function getDescription(): string { return 'The accesskey attribute should not be used as it conflicts with browser and screen reader shortcuts.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if ($element->hasAttribute('accesskey')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the accesskey attribute. Use natural focus order and standard keyboard event listeners instead.'
            );
        }

        return null;
    }
}
