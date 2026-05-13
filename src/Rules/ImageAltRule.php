<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\AbstractRule;

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

    public function getStandardId(): string { return 'WCAG 2.1 1.1.1'; }
    public function getSeverity(): string { return 'Critical'; }
}
