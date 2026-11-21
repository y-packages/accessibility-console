<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaTreePattern extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_TREE';
    }

    public function getDescription(): string
    {
        return 'WAI-ARIA Tree widgets (role="tree") must have an accessible name and expandable treeitems must specify aria-expanded.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        $role = strtolower(trim($element->getAttribute('role')));

        if ($role === 'tree') {
            $hasName = ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '')
                || ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '');
            if (!$hasName) {
                return $this->createViolation(
                    $element,
                    'Element with role="tree" is missing an accessible name (aria-label or aria-labelledby).',
                    'Add aria-label="File Explorer" or aria-labelledby referencing a visible heading.'
                );
            }
            return null;
        }

        if ($role === 'treeitem') {
            $doc = $element->ownerDocument;
            if ($doc !== null) {
                $xpath = new DOMXPath($doc);
                $nestedGroup = $xpath->query('./*[@role="group"] | ./ul | ./ol', $element);
                if ($nestedGroup !== false && $nestedGroup->length > 0 && !$element->hasAttribute('aria-expanded')) {
                    return $this->createViolation(
                        $element,
                        'Parent element with role="treeitem" contains nested child items without an aria-expanded attribute.',
                        'Add aria-expanded="true" or aria-expanded="false" to expandable treeitem nodes.'
                    );
                }
            }
        }

        return null;
    }
}
