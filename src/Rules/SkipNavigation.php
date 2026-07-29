<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SkipNavigation extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_1_SkipNavigation'; }
    public function getDescription(): string { return 'Checks if the page has a skip navigation mechanism.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

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
        
        $hasSkipLink = false;
        $links = $xpath->query('//a[starts-with(@href, "#")]');
        if ($links !== false && $links->length > 0) {
            // Simplified check: if there is an anchor linking to an id, we assume it could be a skip link
            $hasSkipLink = true; 
        }
        
        $mains = $xpath->query('//main | //*[@role="main"]');
        $hasMain = ($mains !== false && $mains->length > 0);
        
        $navs = $xpath->query('//nav[@role="navigation"] | //nav');
        $hasNav = ($navs !== false && $navs->length > 0);
        
        if (!$hasSkipLink && (!$hasMain || !$hasNav)) {
            // Re-evaluating the rule condition:
            // OR look for a <main> element or role="main" anywhere
            // OR look for a <nav> element with role="navigation"
            if (!$hasMain && !$hasNav) {
                return $this->createViolation(
                    $element,
                    'Sayfada atlama mekanizması (skip navigation) bulunamadı.',
                    'Provide a skip link (href="#..."), a <main> element, or a <nav role="navigation"> to allow users to bypass repetitive content.'
                );
            }
        }
        
        return null;
    }
}
