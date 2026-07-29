<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ImageAltRedundant extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_1_1_ALT_REDUNDANT'; }
    public function getDescription(): string { return 'Image alt text should not contain redundant words like "image" or "photo".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }

    /** @var array<int, string> */
    private array $redundantWords = [
        'resmi', 'görüntüsü', 'fotoğrafı', 'grafiği', 'logosu', 'ikonu',
        'image of', 'photo of', 'picture of', 'graphic of', 'logo of', 'icon of',
        'profile photo', 'banner image', 'background image', 'thumbnail of', 'screenshot of', 'avatar of',
        'görseli', 'arkaplan resmi', 'profil fotoğrafı', 'küçük resmi', 'ekran görüntüsü'
    ];

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'img') {
            return null;
        }

        if (!$element->hasAttribute('alt')) {
            return null;
        }

        $alt = trim($element->getAttribute('alt'));
        $cleanAlt = strtolower(preg_replace('/\s+/', ' ', $alt) ?? '');

        if ($cleanAlt === '') {
            return null;
        }

        foreach ($this->redundantWords as $word) {
            if (str_starts_with($cleanAlt, $word) || str_contains($cleanAlt, ' ' . $word)) {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Remove redundant phrases like "' . $word . '" from the alt text. Screen readers already announce elements as images.'
                );
            }
        }

        return null;
    }
}
