<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TimeoutWarning extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_2_1_TIMEOUT_WARNING';
    }

    public function getDescription(): string
    {
        return 'Session timeout or countdown containers must expose role="timer" and provide a mechanism to extend or pause the time limit (WCAG 2.2.1).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        $hasTimeoutAttr = $element->hasAttribute('data-timeout')
            || $element->hasAttribute('data-session-timeout')
            || $element->hasAttribute('data-countdown');

        $idAndClass = strtolower($element->getAttribute('id') . ' ' . $element->getAttribute('class'));
        $looksLikeTimeout = $hasTimeoutAttr
            || (bool)preg_match('/\b(session-timeout|timeout-modal|countdown-timer|inactivity-warning)\b/', $idAndClass);

        if (!$looksLikeTimeout) {
            return null;
        }

        $role = strtolower($element->getAttribute('role'));
        $hasLive = $element->hasAttribute('aria-live') || in_array($role, ['timer', 'alert', 'alertdialog', 'status'], true);

        if (!$hasLive) {
            return $this->createViolation(
                $element,
                'Timeout or countdown container is missing role="timer" or an aria-live announcement region (WCAG 2.2.1 Timing Adjustable).',
                'Add role="timer" aria-live="polite" and ensure a button allows users to extend, pause, or turn off the time limit.'
            );
        }

        // Check for extend/pause/continue button inside timeout modal/container
        if (in_array($role, ['dialog', 'alertdialog'], true) || str_contains($idAndClass, 'timeout')) {
            $doc = $element->ownerDocument;
            if ($doc !== null) {
                $xpath = new DOMXPath($doc);
                $buttons = $xpath->query('.//button | .//a[@href] | .//input[@type="button"]', $element);
                if ($buttons !== false && $buttons->length === 0) {
                    return $this->createViolation(
                        $element,
                        'Timeout warning container provides no interactive control to extend or pause the session time limit (WCAG 2.2.1).',
                        'Add a focusable <button type="button">Extend Session</button> inside the timeout container.'
                    );
                }
            }
        }

        return null;
    }
}
