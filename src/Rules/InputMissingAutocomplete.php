<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InputMissingAutocomplete extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_3_8_ACCESSIBLE_AUTH';
    }

    public function getDescription(): string
    {
        return 'Authentication inputs must support password managers and must not disable autocomplete or paste (WCAG 2.2 SC 3.3.8).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        $type = strtolower($element->getAttribute('type'));

        // 1. Check for paste blocking on input fields (which harms accessible authentication)
        if ($element->hasAttribute('onpaste')) {
            $onpaste = strtolower(trim($element->getAttribute('onpaste')));
            if (str_contains($onpaste, 'return false') || str_contains($onpaste, 'preventdefault')) {
                return $this->createViolation(
                    $element,
                    "Input field blocks pasting (onpaste handler). This violates accessible authentication (WCAG 2.2 SC 3.3.8).",
                    "Remove paste restrictions so users can paste credentials from password managers."
                );
            }
        }

        // 2. Password fields should not have autocomplete="off"
        if ($type === 'password') {
            $autocomplete = strtolower(trim($element->getAttribute('autocomplete')));
            if ($autocomplete === 'off') {
                return $this->createViolation(
                    $element,
                    "Password input has autocomplete=\"off\", preventing password managers from assisting users.",
                    "Change autocomplete to \"current-password\" or \"new-password\" instead of \"off\"."
                );
            }

            if ($autocomplete === '' && !$element->hasAttribute('autocomplete')) {
                return $this->createViolation(
                    $element,
                    "Password input should specify autocomplete=\"current-password\" or autocomplete=\"new-password\".",
                    "Add autocomplete=\"current-password\" (for login) or autocomplete=\"new-password\" (for registration/reset)."
                );
            }
        }

        return null;
    }
}
