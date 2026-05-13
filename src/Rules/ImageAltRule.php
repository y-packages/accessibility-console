<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ImageAltRule extends AbstractRule
{
    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $images = $doc->getElementsByTagName('img');

        foreach ($images as $img) {
            if (!$img->hasAttribute('alt') || trim($img->getAttribute('alt')) === '') {
                $violations[] = $this->createViolation(
                    "Görüntü (img) etiketi 'alt' özniteliğine sahip değil veya boş. WCAG 1.1.1 uyarınca her görüntünün bir açıklaması olmalıdır.",
                    $img
                );
            }
        }
        return $violations;
    }

    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::CRITICAL; }
}
