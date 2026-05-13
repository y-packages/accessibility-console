<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class EmptyLink extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_4_LINK_TEXT'; }
    public function getDescription(): string { return 'Links must have discernible text.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a') {
            return null;
        }

        $text = trim($element->textContent);
        if ($text === '' && !$element->hasAttribute('aria-label') && !$element->hasAttribute('title')) {
            // Check for images inside
            $imgs = $element->getElementsByTagName('img');
            foreach ($imgs as $img) {
                if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') {
                    return null;
                }
            }
            
            return $this->createViolation($element, $this->getDescription(), 'Add text content or an aria-label to the link.');
        }

        return null;
    }
}
