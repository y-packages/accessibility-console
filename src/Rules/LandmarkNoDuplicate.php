<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LandmarkNoDuplicate extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LandmarkNoDuplicate'; }
    public function getDescription(): string { return 'Checks that if there are multiple landmarks of the same type, each has a unique label.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 5; }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['nav', 'aside', 'section', 'form'])) {
            return null;
        }
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $elementsOfSameType = $xpath->query('//' . $tag);
        
        if ($elementsOfSameType === false || $elementsOfSameType->length <= 1) {
            return null;
        }
        
        $label = $element->getAttribute('aria-label');
        $labelledby = $element->getAttribute('aria-labelledby');
        
        if (empty($label) && empty($labelledby)) {
            return $this->createViolation(
                $element,
                "Birden fazla <{$tag}> öğesi var ancak bu öğenin benzersiz bir etiketi (aria-label/aria-labelledby) yok.",
                "Provide a unique aria-label or aria-labelledby for each <{$tag}> when multiple exist."
            );
        }
        
        // Check for duplicates
        $duplicateLabel = false;
        foreach ($elementsOfSameType as $el) {
            if (!$el instanceof DOMElement) {
                continue;
            }
            if (!$el->isSameNode($element)) {
                $otherLabel = $el->getAttribute('aria-label');
                $otherLabelledby = $el->getAttribute('aria-labelledby');
                
                if (!empty($label) && $otherLabel === $label) {
                    $duplicateLabel = true;
                    break;
                }
                
                if (!empty($labelledby) && $otherLabelledby === $labelledby) {
                    $duplicateLabel = true;
                    break;
                }
            }
        }
        
        if ($duplicateLabel) {
            return $this->createViolation(
                $element,
                "Birden fazla <{$tag}> öğesi aynı etiketi kullanıyor.",
                "Ensure aria-label or aria-labelledby is unique for each <{$tag}>."
            );
        }
        
        return null;
    }
}
