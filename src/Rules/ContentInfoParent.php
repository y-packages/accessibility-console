<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ContentInfoParent extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_CONTENTINFO_PARENT'; }
    public function getDescription(): string { return 'Footer contentinfo landmark must be at top-level body or within valid sectioning root.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'footer' && $element->getAttribute('role') !== 'contentinfo') {
            return null;
        }

        $parent = $element->parentNode;
        while ($parent instanceof DOMElement) {
            $pTag = strtolower($parent->tagName);
            if (in_array($pTag, ['article', 'aside', 'main', 'nav', 'section'], true)) {
                return $this->createViolation(
                    $element,
                    sprintf('Footer contentinfo landmark is nested inside <%s> sectioning element.', $pTag),
                    'Place global footer contentinfo landmark at the top level of <body> rather than nested inside sectioning elements.'
                );
            }
            $parent = $parent->parentNode;
        }

        return null;
    }
}
