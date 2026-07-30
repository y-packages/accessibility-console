<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AppletBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_APPLET_BANNED'; }
    public function getDescription(): string { return 'The <applet> element is obsolete and non-accessible. Use modern HTML5 elements instead.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) === 'applet') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove the <applet> element and replace it with modern HTML5 or accessible media embeddings.'
            );
        }

        return null;
    }
}
