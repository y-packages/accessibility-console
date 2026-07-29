<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LandmarkContentInfo extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LandmarkContentInfo'; }
    public function getDescription(): string { return 'Checks that there is at most one page-level <footer> element.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'footer') {
            return null;
        }
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $ancestors = $xpath->query('ancestor::article | ancestor::section | ancestor::aside | ancestor::nav | ancestor::main', $element);
        
        if ($ancestors !== false && $ancestors->length > 0) {
            return null; // It's not a page-level footer
        }
        
        $footers = $xpath->query('//footer');
        if ($footers === false) {
            return null;
        }

        $index = 0;
        foreach ($footers as $footer) {
            if (!$footer instanceof \DOMNode) {
                continue;
            }
            $anc = $xpath->query('ancestor::article | ancestor::section | ancestor::aside | ancestor::nav | ancestor::main', $footer);
            if ($anc !== false && $anc->length === 0) {
                $index++;
                if ($footer->isSameNode($element) && $index > 1) {
                    return $this->createViolation(
                        $element,
                        'Sayfada birden fazla ana (top-level) <footer> bulundu.',
                        'Ensure there is at most one top-level <footer> element (contentinfo landmark) on the page.'
                    );
                }
            }
        }
        
        return null;
    }
}
