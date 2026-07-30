<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaExpandedState extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ARIA_EXPANDED'; }
    public function getDescription(): string { return 'Collapsible toggle buttons (accordion, dropdown, collapse) must have an aria-expanded attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        $tagName = strtolower($element->tagName);
        $role = strtolower($element->getAttribute('role'));

        if ($tagName !== 'button' && $tagName !== 'a' && $role !== 'button') {
            return null;
        }

        $class = strtolower($element->getAttribute('class'));
        $dataBsToggle = strtolower($element->getAttribute('data-bs-toggle'));
        $dataToggle = strtolower($element->getAttribute('data-toggle'));

        $isCollapsible = str_contains($class, 'dropdown-toggle') || 
                         str_contains($class, 'accordion-button') || 
                         str_contains($class, 'collapse-toggle') ||
                         in_array($dataBsToggle, ['collapse', 'dropdown', 'offcanvas'], true) ||
                         in_array($dataToggle, ['collapse', 'dropdown'], true);

        if (!$isCollapsible) {
            return null;
        }

        if ($element->hasAttribute('aria-expanded')) {
            $val = strtolower($element->getAttribute('aria-expanded'));
            if (in_array($val, ['true', 'false'], true)) {
                return null;
            }
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add aria-expanded="false" (or "true") to the toggle element so screen readers announce expanded/collapsed status.'
        );
    }
}
