<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FocusableWithoutRole extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_FOCUSABLE_ROLE'; }
    public function getDescription(): string { return 'Non-interactive focusable elements (with tabindex >= 0) must have an explicit ARIA role.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('tabindex')) {
            return null;
        }

        $tabindex = (int)$element->getAttribute('tabindex');
        if ($tabindex < 0) {
            return null;
        }

        $tagName = strtolower($element->tagName);
        $nonInteractiveTags = ['div', 'span', 'p', 'li', 'section', 'article', 'td', 'th', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        
        if (in_array($tagName, $nonInteractiveTags, true) && !$element->hasAttribute('role')) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                sprintf('Add an appropriate ARIA role (e.g., role="button", role="link") to the focusable <%s> element.', $tagName)
            );
        }

        return null;
    }
}
