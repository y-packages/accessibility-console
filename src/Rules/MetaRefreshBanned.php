<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MetaRefreshBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_2_1_META_REFRESH'; }
    public function getDescription(): string { return 'Do not use meta http-equiv="refresh" for automatic page redirects or updates as it disorients users.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 1; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'meta') {
            return null;
        }

        if ($element->hasAttribute('http-equiv')) {
            $httpEquiv = trim($element->getAttribute('http-equiv'));
            if (strtolower($httpEquiv) === 'refresh') {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Use server-side redirects (e.g. HTTP 301/302) or standard JavaScript routing instead of meta refresh.'
                );
            }
        }

        return null;
    }
}
