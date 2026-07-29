<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AutocompleteValidTokens extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_5_AUTOCOMPLETE_VALID_TOKENS'; }
    public function getDescription(): string { return 'Autocomplete attribute must use valid token values.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['input', 'select', 'textarea'])) {
            return null;
        }

        if (!$element->hasAttribute('autocomplete')) {
            return null;
        }

        $autocompleteValue = trim($element->getAttribute('autocomplete'));
        if ($autocompleteValue === '') {
            return null;
        }

        // Handle templates like {{ value }} or template syntax
        if (preg_match('/^(\{\{|\{%).*(\}\}|\%\})$/', $autocompleteValue) || preg_match('/^' . preg_quote('<' . '?', '/') . '.*' . preg_quote('?' . '>', '/') . '$/', $autocompleteValue)) {
            return null;
        }

        $tokens = explode(' ', strtolower($autocompleteValue));
        
        $validBaseTokens = [
            'off', 'on', 'name', 'honorific-prefix', 'given-name', 'additional-name', 
            'family-name', 'honorific-suffix', 'nickname', 'email', 'username', 
            'new-password', 'current-password', 'one-time-code', 'organization-title', 
            'organization', 'street-address', 'address-line1', 'address-line2', 
            'address-line3', 'address-level4', 'address-level3', 'address-level2', 
            'address-level1', 'country', 'country-name', 'postal-code', 'cc-name', 
            'cc-given-name', 'cc-additional-name', 'cc-family-name', 'cc-number', 
            'cc-exp', 'cc-exp-month', 'cc-exp-year', 'cc-csc', 'cc-type', 
            'transaction-currency', 'transaction-amount', 'language', 'bday', 
            'bday-day', 'bday-month', 'bday-year', 'sex', 'tel', 'tel-country-code', 
            'tel-national', 'tel-area-code', 'tel-local', 'tel-extension', 'impp', 
            'url', 'photo'
        ];

        foreach ($tokens as $token) {
            if (empty($token)) continue;
            
            // Allow section-* prefixes
            if (strpos($token, 'section-') === 0) {
                continue;
            }
            
            // Allow shipping/billing prefixes
            if ($token === 'shipping' || $token === 'billing') {
                continue;
            }
            
            // Check for valid base tokens
            // Additional check: some tokens like 'tel' can be prefixed, we handle it simply by checking if it's in the list
            $isValid = false;
            foreach ($validBaseTokens as $baseToken) {
                if ($token === $baseToken || str_ends_with($token, "-{$baseToken}")) {
                    $isValid = true;
                    break;
                }
            }
            
            if (!$isValid) {
                return $this->createViolation(
                    $element,
                    "Geçersiz autocomplete değeri veya belirteci: '{$token}'.",
                    "Geçerli bir HTML5 autocomplete belirteci kullanın (örneğin: name, email, tel)."
                );
            }
        }

        return null;
    }
}
