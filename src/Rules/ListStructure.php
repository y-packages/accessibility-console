<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ListStructure extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LIST_STRUCTURE'; }
    public function getDescription(): string { return 'Lists (ul, ol) must only contain list item elements (li) as direct children.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if ($tag !== 'ul' && $tag !== 'ol') {
            return null;
        }

        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $childTag = strtolower($child->tagName);
                if (!in_array($childTag, ['li', 'script', 'template'], true)) {
                    return $this->createViolation(
                        $element,
                        sprintf('List element <%s> contains invalid direct child <%s>. Only <li> elements are allowed.', $tag, $childTag),
                        'Wrap the child element in an <li> tag, or move it outside the list structure.'
                    );
                }
            }
        }

        return null;
    }
}
