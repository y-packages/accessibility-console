<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\AbstractRule;

class HeadingOrderRule extends AbstractRule
{
    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $xpath = new \DOMXPath($doc);
        $headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');
        
        $lastLevel = 0;
        foreach ($headings as $h) {
            $level = (int)substr($h->tagName, 1);
            if ($lastLevel > 0 && $level > $lastLevel + 1) {
                $violations[] = $this->createViolation(
                    "Başlık hiyerarşisi bozuk. H{$lastLevel} etiketinden doğrudan H{$level} etiketine geçiş yapılmış. Sıralı olmalıdır.",
                    $h
                );
            }
            $lastLevel = $level;
        }
        return $violations;
    }

    public function getStandardId(): string { return 'WCAG 2.1 1.3.1'; }
    public function getSeverity(): string { return 'Major'; }
}
