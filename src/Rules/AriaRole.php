<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaRole extends AbstractRule
{
    private static array $validRoles = [
        'alert', 'alertdialog', 'application', 'article', 'banner', 'button', 'cell', 'checkbox',
        'columnheader', 'combobox', 'complementary', 'contentinfo', 'definition', 'dialog', 'directory',
        'document', 'feed', 'figure', 'form', 'grid', 'gridcell', 'group', 'heading', 'img', 'link',
        'list', 'listbox', 'listitem', 'log', 'main', 'marquee', 'math', 'menu', 'menubar', 'menuitem',
        'menuitemcheckbox', 'menuitemradio', 'navigation', 'none', 'note', 'option', 'presentation',
        'progressbar', 'radio', 'radiogroup', 'region', 'row', 'rowgroup', 'rowheader', 'scrollbar',
        'search', 'searchbox', 'separator', 'slider', 'spinbutton', 'status', 'switch', 'tab', 'table',
        'tablist', 'tabpanel', 'term', 'textbox', 'timer', 'toolbar', 'tooltip', 'tree', 'treegrid',
        'treeitem'
    ];

    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $xpath = new \DOMXPath($doc);
        
        // 1. Check WAI-ARIA Role Validity
        $roleElements = $xpath->query('//*[@role]');
        foreach ($roleElements as $el) {
            if (!$el instanceof \DOMElement) {
                continue;
            }
            $role = trim($el->getAttribute('role'));
            if ($role === '') {
                continue;
            }
            $roles = array_filter(explode(' ', $role));
            foreach ($roles as $r) {
                if (!in_array(strtolower($r), self::$validRoles, true)) {
                    $violations[] = $this->createViolation(
                        "Invalid ARIA role \"{$r}\" declared on element. Must use standard WAI-ARIA roles.",
                        $el
                    );
                }
            }
        }

        // 2. Check aria-controls Targets Existence
        $controlsElements = $xpath->query('//*[@aria-controls]');
        foreach ($controlsElements as $el) {
            if (!$el instanceof \DOMElement) {
                continue;
            }
            $targetsStr = trim($el->getAttribute('aria-controls'));
            if ($targetsStr === '') {
                continue;
            }
            $targets = array_filter(explode(' ', $targetsStr));
            foreach ($targets as $targetId) {
                $targetQuery = $xpath->query('//*[@id="' . $targetId . '"]');
                if ($targetQuery->length === 0) {
                    $violations[] = $this->createViolation(
                        "Attribute aria-controls=\"{$targetId}\" targets an element that does not exist in the document.",
                        $el
                    );
                }
            }
        }

        return $violations;
    }

    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
}
