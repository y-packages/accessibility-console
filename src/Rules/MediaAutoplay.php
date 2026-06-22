<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MediaAutoplay extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_4_2_AUTOPLAY'; }
    public function getDescription(): string { return 'Audio and video elements must not autoplay with sound, as it interferes with screen readers and user control.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        $tagName = strtolower($element->tagName);
        if ($tagName !== 'video' && $tagName !== 'audio') {
            return null;
        }

        if ($element->hasAttribute('autoplay')) {
            // If autoplay is active, it must be muted to be acceptable
            if (!$element->hasAttribute('muted')) {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Remove the autoplay attribute, or add the muted attribute to prevent the media from playing sound automatically.'
                );
            }
        }

        return null;
    }
}
