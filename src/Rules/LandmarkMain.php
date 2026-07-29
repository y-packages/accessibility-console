<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LandmarkMain extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LandmarkMain'; }
    public function getDescription(): string { return 'Checks if the page has exactly one <main> element or element with role="main".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'html') {
            return null;
        }
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $mains = $xpath->query('//main | //*[@role="main"]');
        
        if ($mains === false || $mains->length === 0) {
            return $this->createViolation(
                $element,
                'Sayfada <main> alanı bulunamadı.',
                'Add exactly one <main> element or element with role="main" to the page.'
            );
        } elseif ($mains->length > 1) {
            return $this->createViolation(
                $element,
                'Sayfada birden fazla <main> alanı bulundu.',
                'Ensure there is only one <main> element or element with role="main" per page.'
            );
        }
        
        return null;
    }
}
