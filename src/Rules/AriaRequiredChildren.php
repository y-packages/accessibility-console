<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaRequiredChildren extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_ARIA_REQUIRED_CHILDREN'; }
    public function getDescription(): string { return 'Elements with certain ARIA roles must contain children with specific roles.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('role')) {
            return null;
        }

        $roles = explode(' ', strtolower(trim($element->getAttribute('role'))));
        $requiredChildrenMap = [
            'list' => ['listitem'],
            'menu' => ['menuitem', 'menuitemcheckbox', 'menuitemradio'],
            'menubar' => ['menuitem', 'menuitemcheckbox', 'menuitemradio'],
            'tablist' => ['tab'],
            'tree' => ['treeitem'],
            'grid' => ['row', 'rowgroup'],
            'table' => ['row', 'rowgroup'],
            'radiogroup' => ['radio'],
            'listbox' => ['option'],
            'rowgroup' => ['row'],
            'row' => ['cell', 'gridcell', 'columnheader', 'rowheader']
        ];

        foreach ($roles as $role) {
            if (isset($requiredChildrenMap[$role])) {
                $hasRequiredChild = $this->hasChildWithRole($element, $requiredChildrenMap[$role]);
                
                if (!$hasRequiredChild) {
                    // Check if children are dynamically generated with templates
                    $doc = $element->ownerDocument;
                    if ($doc !== null) {
                        $html = $doc->saveHTML($element);
                        if ($html !== false && preg_match('/(\{\{|\{%|<\?)/', $html)) {
                            continue;
                        }
                    }

                    $expectedStr = implode(' veya ', $requiredChildrenMap[$role]);
                    return $this->createViolation(
                        $element,
                        "ARIA rolü '{$role}' olan eleman gerekli alt rollere sahip değil.",
                        "Bu elemanın içine rolü {$expectedStr} olan çocuk elemanlar ekleyin."
                    );
                }
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $requiredRoles
     */
    private function hasChildWithRole(DOMElement $element, array $requiredRoles): bool
    {
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return false;
        }
        $xpath = new \DOMXPath($doc);
        foreach ($requiredRoles as $reqRole) {
            $query = ".//*[@role='{$reqRole}' or contains(@role, ' {$reqRole}') or contains(@role, '{$reqRole} ')]";
            $nodes = $xpath->query($query, $element);
            if ($nodes !== false && $nodes->length > 0) {
                return true;
            }
        }
        return false;
    }
}
