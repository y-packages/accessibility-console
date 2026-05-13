<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\AbstractRule;

class EmptyLinkRule extends AbstractRule
{
    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $links = $doc->getElementsByTagName('a');

        foreach ($links as $link) {
            if (trim($link->textContent) === '' && !$link->hasAttribute('aria-label')) {
                $violations[] = $this->createViolation(
                    "Bağlantı (a) etiketi metin içermiyor ve 'aria-label' tanımlanmamış. Ekran okuyucular bu linkin nereye gittiğini anlayamaz.",
                    $link
                );
            }
        }
        return $violations;
    }

    public function getStandardId(): string { return 'WCAG 2.1 2.4.4'; }
    public function getSeverity(): string { return 'Critical'; }
}
