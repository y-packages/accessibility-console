<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LinkTargetBlank extends AbstractRule
{
    public function getId(): string { return 'WCAG_LINK_TARGET_BLANK'; }
    public function getDescription(): string { return 'Links with target="_blank" should have rel="noopener" or rel="noreferrer".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a') {
            return null;
        }

        if (strtolower($element->getAttribute('target')) === '_blank') {
            $rel = strtolower($element->getAttribute('rel'));
            if (!str_contains($rel, 'noopener') && !str_contains($rel, 'noreferrer')) {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Add rel="noopener" or rel="noreferrer" to the link to improve security, performance, and screen reader notification.'
                );
            }
        }

        return null;
    }
}
