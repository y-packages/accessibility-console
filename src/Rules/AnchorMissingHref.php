<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AnchorMissingHref extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ANCHOR_HREF'; }
    public function getDescription(): string { return 'Anchor elements (<a>) acting as links must have a valid href attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a') {
            return null;
        }

        if ($element->hasAttribute('name') || $element->hasAttribute('role')) {
            return null;
        }

        if (!$element->hasAttribute('href') || trim($element->getAttribute('href')) === '') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add a valid href attribute to the <a> element, or use a <button> element if it triggers an action.'
            );
        }

        return null;
    }
}
