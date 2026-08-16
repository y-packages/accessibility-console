<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MediaControls extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_4_2_MEDIA_CONTROLS';
    }

    public function getDescription(): string
    {
        return 'Audio and video elements should provide controls for user interaction.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if ($tag !== 'audio' && $tag !== 'video') {
            return null;
        }

        // Check if controls attribute is present
        if ($element->hasAttribute('controls')) {
            return null;
        }

        // Allow background video if explicitly muted and looping
        $isMuted = $element->hasAttribute('muted');
        $isAutoplay = $element->hasAttribute('autoplay');
        $isLoop = $element->hasAttribute('loop');

        if ($tag === 'video' && $isMuted && $isAutoplay && $isLoop) {
            return null;
        }

        return $this->createViolation(
            $element,
            "<{$tag}> element is missing the 'controls' attribute.",
            "Add the 'controls' attribute to allow users to play, pause, and adjust media volume."
        );
    }
}
