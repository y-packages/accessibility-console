<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ImageAltPlaceholder extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_ALT_PLACEHOLDER'; }
    public function getDescription(): string { return 'Image alt text should not be a generic placeholder or filename.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'img') {
            return null;
        }

        if (!$element->hasAttribute('alt')) {
            return null;
        }

        $alt = trim($element->getAttribute('alt'));
        if ($alt === '') {
            return null; // Empty alt is allowed for decorative images (WCAG 1.1.1)
        }

        $cleanAlt = strtolower($alt);

        // Generic placeholder checks
        $placeholders = [
            'image', 'photo', 'picture', 'logo', 'icon', 'placeholder', 'untitled', 'temp', 'blank', 'pic', 'dot',
            'resim', 'foto', 'fotoğraf', 'görsel', 'grafik', 'ikon', 'logo'
        ];
        if (in_array($cleanAlt, $placeholders, true)) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Provide a specific description of what the image shows, rather than using a generic word.'
            );
        }

        // Filename checks
        if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|bmp)$/i', $cleanAlt)) {
            return $this->createViolation(
                $element,
                'Image alt text should not be a filename.',
                'Provide a descriptive alternative text instead of the image filename.'
            );
        }

        return null;
    }
}
