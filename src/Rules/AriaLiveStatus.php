<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaLiveStatus extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_3_STATUS_MESSAGES'; }
    public function getDescription(): string { return 'Dynamic status containers and toast messages must use aria-live or implicit live roles.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        $class = strtolower($element->getAttribute('class'));
        $role = strtolower($element->getAttribute('role'));

        $statusClasses = ['toast', 'status-message', 'loading-message', 'alert-message', 'live-region'];
        $isStatusContainer = false;

        foreach ($statusClasses as $statusClass) {
            if (str_contains($class, $statusClass)) {
                $isStatusContainer = true;
                break;
            }
        }

        if (!$isStatusContainer) {
            return null;
        }

        // Implicit live roles in ARIA include status, alert, log
        if (in_array($role, ['status', 'alert', 'log'], true)) {
            return null;
        }

        if ($element->hasAttribute('aria-live')) {
            $liveVal = strtolower($element->getAttribute('aria-live'));
            if (in_array($liveVal, ['polite', 'assertive'], true)) {
                return null;
            }
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add aria-live="polite" (or role="status"/role="alert") so screen readers dynamically announce updates to users.'
        );
    }
}
