<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class DuplicateH1 extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_DUPLICATE_H1'; }
    public function getDescription(): string { return 'Pages should not contain more than one h1 element.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'h1') {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc) {
            $h1s = $doc->getElementsByTagName('h1');
            if ($h1s->length > 1 && $h1s->item(0) !== $element) {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Change this heading to an h2 or lower level heading, keeping only one h1 per page.'
                );
            }
        }

        return null;
    }
}
