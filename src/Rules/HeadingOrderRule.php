<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

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

    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
}
