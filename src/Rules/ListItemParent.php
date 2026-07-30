<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ListItemParent extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LIST_ITEM_PARENT'; }
    public function getDescription(): string { return 'List items (<li>) must be contained inside a parent <ul>, <ol>, or <menu> element.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'li') {
            return null;
        }

        $parent = $element->parentNode;
        if (!$parent instanceof DOMElement) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Wrap the orphan <li> element in a parent <ul> or <ol> tag.'
            );
        }

        $parentTag = strtolower($parent->tagName);
        $validParents = ['ul', 'ol', 'menu'];

        if (!in_array($parentTag, $validParents, true) && strtolower($parent->getAttribute('role')) !== 'list') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                sprintf('Move the <li> element inside a <ul> or <ol> container instead of <%s>.', $parentTag)
            );
        }

        return null;
    }
}
