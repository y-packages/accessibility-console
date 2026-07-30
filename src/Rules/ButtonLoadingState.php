<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ButtonLoadingState extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_BUTTON_LOADING_STATE'; }
    public function getDescription(): string { return 'Buttons displaying loading spinners must inform screen readers via aria-busy or aria-label.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $tagName = strtolower($element->tagName);
        $role = strtolower($element->getAttribute('role'));

        if ($tagName !== 'button' && $role !== 'button') {
            return null;
        }

        $class = strtolower($element->getAttribute('class'));
        $hasLoadingClass = str_contains($class, 'loading') || str_contains($class, 'spinner') || str_contains($class, 'is-busy');
        
        $hasSpinnerChild = false;
        foreach ($element->getElementsByTagName('*') as $child) {
            $childClass = strtolower($child->getAttribute('class'));
            if (str_contains($childClass, 'spinner') || str_contains($childClass, 'loading') || str_contains($childClass, 'loader')) {
                $hasSpinnerChild = true;
                break;
            }
        }

        if (!$hasLoadingClass && !$hasSpinnerChild && strtolower($element->getAttribute('aria-busy')) !== 'true') {
            return null;
        }

        // Must have aria-busy="true" or an explicit aria-label/aria-live describing the loading process
        $hasAriaBusy = strtolower($element->getAttribute('aria-busy')) === 'true';
        $hasAriaLive = $element->hasAttribute('aria-live');
        $hasAriaLabel = $element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '';

        if ($hasAriaBusy || ($hasAriaLabel && $hasAriaLive)) {
            return null;
        }

        return $this->createViolation(
            $element,
            $this->getDescription(),
            'Add aria-busy="true" and an aria-label (e.g. aria-label="Loading...") to the button when an async action is in progress.'
        );
    }
}
