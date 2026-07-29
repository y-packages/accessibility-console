<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class DefinitionListStructure extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_DefinitionListStructure'; }
    public function getDescription(): string { return 'Checks that <dl> elements only contain <dt>, <dd>, and <div> as direct children.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'dl') {
            return null;
        }
        
        $invalidChildren = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $childTag = strtolower($child->tagName);
                if (!in_array($childTag, ['dt', 'dd', 'div'])) {
                    $invalidChildren[] = $childTag;
                }
            }
        }
        
        if (count($invalidChildren) > 0) {
            return $this->createViolation(
                $element,
                '<dl> öğesi doğrudan alt öğe olarak sadece <dt>, <dd> veya <div> içerebilir.',
                'Remove or wrap invalid children (' . implode(', ', array_unique($invalidChildren)) . ') inside <dd> or <dt>.'
            );
        }
        
        return null;
    }
}
