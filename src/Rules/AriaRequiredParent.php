<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaRequiredParent extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_ARIA_REQUIRED_PARENT'; }
    public function getDescription(): string { return 'Elements with certain ARIA roles must be contained by parents with specific roles.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (!$element->hasAttribute('role')) {
            return null;
        }

        $roles = explode(' ', strtolower(trim($element->getAttribute('role'))));
        $requiredParentMap = [
            'listitem' => ['list', 'group'],
            'menuitem' => ['menu', 'menubar', 'group'],
            'menuitemcheckbox' => ['menu', 'menubar'],
            'menuitemradio' => ['menu', 'menubar'],
            'tab' => ['tablist'],
            'treeitem' => ['tree', 'group'],
            'cell' => ['row'],
            'gridcell' => ['row'],
            'columnheader' => ['row'],
            'rowheader' => ['row'],
            'option' => ['listbox', 'group'],
            'row' => ['grid', 'table', 'treegrid', 'rowgroup']
        ];

        foreach ($roles as $role) {
            if (isset($requiredParentMap[$role])) {
                $hasRequiredParent = $this->hasParentWithRole($element, $requiredParentMap[$role]);
                
                if (!$hasRequiredParent) {
                    $expectedStr = implode(' veya ', $requiredParentMap[$role]);
                    return $this->createViolation(
                        $element,
                        "ARIA rolü '{$role}' olan eleman gerekli üst eleman rollerinden hiçbirine sahip değil.",
                        "Bu elemanı rolü {$expectedStr} olan bir üst elemanın içine yerleştirin."
                    );
                }
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $requiredRoles
     */
    private function hasParentWithRole(DOMElement $element, array $requiredRoles): bool
    {
        $parent = $element->parentNode;
        while ($parent !== null && $parent instanceof DOMElement) {
            if ($parent->hasAttribute('role')) {
                $parentRoles = explode(' ', strtolower(trim($parent->getAttribute('role'))));
                foreach ($parentRoles as $pRole) {
                    if (in_array($pRole, $requiredRoles)) {
                        return true;
                    }
                }
            }
            // For native HTML semantics fallback (e.g. <ul> is a list)
            $tagName = strtolower($parent->tagName);
            $nativeRole = $this->getNativeRole($tagName);
            if ($nativeRole && in_array($nativeRole, $requiredRoles)) {
                return true;
            }
            
            $parent = $parent->parentNode;
        }
        return false;
    }
    
    private function getNativeRole(string $tagName): ?string
    {
        $map = [
            'ul' => 'list',
            'ol' => 'list',
            'dl' => 'list',
            'table' => 'table',
            'tr' => 'row',
            'tbody' => 'rowgroup',
            'thead' => 'rowgroup',
            'tfoot' => 'rowgroup',
            'select' => 'listbox',
            'optgroup' => 'group'
        ];
        return $map[$tagName] ?? null;
    }
}
