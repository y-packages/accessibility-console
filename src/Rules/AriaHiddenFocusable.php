<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaHiddenFocusable extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ARIA_HIDDEN_FOCUS'; }
    public function getDescription(): string { return 'Focusable elements must not be hidden with aria-hidden="true".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }

    public function check(DOMElement $element): ?Violation
    {
        if ($element->getAttribute('aria-hidden') !== 'true') {
            return null;
        }

        if ($this->isFocusable($element) || $this->hasFocusableDescendant($element)) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Remove aria-hidden="true" from the container or make its focusable elements non-focusable (e.g. by setting tabindex="-1" or disabled).'
            );
        }

        return null;
    }

    private function isFocusable(DOMElement $element): bool
    {
        $tagName = strtolower($element->tagName);

        if ($element->hasAttribute('tabindex')) {
            $tabindex = (int)$element->getAttribute('tabindex');
            if ($tabindex < 0) {
                return false;
            }
            return true;
        }

        if ($tagName === 'a' && $element->hasAttribute('href')) {
            return true;
        }

        if (in_array($tagName, ['button', 'select', 'textarea'], true)) {
            return !$element->hasAttribute('disabled');
        }

        if ($tagName === 'input') {
            $type = strtolower($element->getAttribute('type'));
            return $type !== 'hidden' && !$element->hasAttribute('disabled');
        }

        if ($tagName === 'iframe') {
            return true;
        }

        return false;
    }

    private function hasFocusableDescendant(DOMElement $element): bool
    {
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return false;
        }

        $xpath = new \DOMXPath($doc);
        $descendants = $xpath->query('.//*', $element);
        if ($descendants === false) {
            return false;
        }

        foreach ($descendants as $descendant) {
            if ($descendant instanceof DOMElement && $this->isFocusable($descendant)) {
                return true;
            }
        }

        return false;
    }
}
