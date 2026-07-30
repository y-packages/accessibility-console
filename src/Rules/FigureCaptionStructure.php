<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FigureCaptionStructure extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_FIGCAPTION_PARENT'; }
    public function getDescription(): string { return '<figcaption> elements must be direct children of a <figure> element.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'figcaption') {
            return null;
        }

        $parent = $element->parentNode;
        if (!$parent instanceof DOMElement || strtolower($parent->tagName) !== 'figure') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Wrap the <figcaption> element inside a parent <figure> element or change it to a standard paragraph/caption element.'
            );
        }

        return null;
    }
}
