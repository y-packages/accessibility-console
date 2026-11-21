<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaActivedescendantValid extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_ACTIVEDESCENDANT';
    }

    public function getDescription(): string
    {
        return 'The aria-activedescendant attribute must reference an existing element ID and be placed on a focusable composite widget.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
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
        if (!$element->hasAttribute('aria-activedescendant')) {
            return null;
        }

        $targetId = trim($element->getAttribute('aria-activedescendant'));
        if ($targetId === '' || preg_match('/\{\{|\{%|<\?php/', $targetId)) {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9_\-:.]+$/', $targetId)) {
            $xpath = new DOMXPath($doc);
            $found = $xpath->query('//*[@id="' . $targetId . '"]');
            if ($found !== false && $found->length === 0) {
                return $this->createViolation(
                    $element,
                    sprintf('Attribute aria-activedescendant="%s" references a non-existent element ID.', $targetId),
                    sprintf('Ensure an element with id="%s" exists inside or is owned by this composite widget.', $targetId)
                );
            }
        }

        $tag = strtolower($element->tagName);
        $nativelyFocusable = in_array($tag, ['input', 'select', 'textarea', 'button', 'a'], true);
        $hasTabindex = $element->hasAttribute('tabindex');

        if (!$nativelyFocusable && !$hasTabindex) {
            return $this->createViolation(
                $element,
                sprintf('Element <%s> uses aria-activedescendant="%s" without being keyboard focusable (missing tabindex="0").', $tag, $targetId),
                'Add tabindex="0" to the composite widget container so keyboard focus can manage the active descendant.'
            );
        }

        return null;
    }
}
