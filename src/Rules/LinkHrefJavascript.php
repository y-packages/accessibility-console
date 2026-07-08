<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LinkHrefJavascript extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_1_1_LINK_JAVASCRIPT'; }
    public function getDescription(): string { return 'Links must not use javascript: void(0) or # as their href. Use buttons for scripting actions.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a') {
            return null;
        }

        if ($element->hasAttribute('href')) {
            $href = trim($element->getAttribute('href'));
            
            // Check for javascript: links or '#' (which represents a placeholder/scripting target)
            if (str_starts_with(strtolower($href), 'javascript:') || $href === '#') {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Convert the link to a <button> element or provide a valid destination URL.'
                );
            }
        }

        return null;
    }
}
