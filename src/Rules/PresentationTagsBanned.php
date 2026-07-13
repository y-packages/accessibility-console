<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class PresentationTagsBanned extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_PRESENTATION_TAGS'; }
    public function getDescription(): string { return 'Obsolete presentation tags (<center>, <strike>, <basefont>, <tt>, <big>) should not be used.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        $bannedTags = ['center', 'strike', 'basefont', 'tt', 'big'];
        $tag = strtolower($element->tagName);
        if (in_array($tag, $bannedTags, true)) {
            return $this->createViolation(
                $element,
                sprintf('The <%s> presentation tag is obsolete and should not be used.', $tag),
                sprintf('Remove the <%s> tag and use modern CSS styling instead.', $tag)
            );
        }

        return null;
    }
}
