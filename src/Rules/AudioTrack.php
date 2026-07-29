<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AudioTrack extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_2_1_AudioTrack'; }
    public function getDescription(): string { return 'Checks that <audio> elements have a <track> child element for captions/descriptions.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'audio') {
            return null;
        }
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $tracks = $xpath->query('./track', $element);
        
        if ($tracks === false || $tracks->length === 0) {
            return $this->createViolation(
                $element,
                '<audio> öğesi <track> alt öğesi içermiyor.',
                'Add a <track> element for captions or provide an accessible transcript.'
            );
        }
        
        return null;
    }
}
