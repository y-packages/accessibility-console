<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LandmarkBanner extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LandmarkBanner'; }
    public function getDescription(): string { return 'Checks that there is at most one page-level <header> element.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'header') {
            return null;
        }
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $ancestors = $xpath->query('ancestor::article | ancestor::section | ancestor::aside | ancestor::nav | ancestor::main', $element);
        
        if ($ancestors !== false && $ancestors->length > 0) {
            return null; // It's not a page-level banner
        }
        
        $headers = $xpath->query('//header');
        if ($headers === false) {
            return null;
        }

        $index = 0;
        foreach ($headers as $header) {
            if (!$header instanceof \DOMNode) {
                continue;
            }
            $anc = $xpath->query('ancestor::article | ancestor::section | ancestor::aside | ancestor::nav | ancestor::main', $header);
            if ($anc !== false && $anc->length === 0) {
                $index++;
                if ($header->isSameNode($element) && $index > 1) {
                    return $this->createViolation(
                        $element,
                        'Sayfada birden fazla ana (top-level) <header> bulundu.',
                        'Ensure there is at most one top-level <header> element (banner landmark) on the page.'
                    );
                }
            }
        }
        
        return null;
    }
}
