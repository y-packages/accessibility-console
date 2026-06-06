<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class VideoTrack extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_2_2_VIDEO_TRACK'; }
    public function getDescription(): string { return 'Video elements must contain at least one track element for captions or subtitles.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'video') {
            return null;
        }

        // Check if there is a track element inside
        $tracks = $element->getElementsByTagName('track');
        if ($tracks->length === 0) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add a <track kind="captions"> or <track kind="subtitles"> element inside the video element.'
            );
        }

        return null;
    }
}
