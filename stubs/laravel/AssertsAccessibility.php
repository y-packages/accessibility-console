<?php

namespace YakNet\AccessibilityConsole\Stubs\Laravel;

use YakNet\AccessibilityConsole\Core\Scanner;

trait AssertsAccessibility
{
    /**
     * Assert that an HTML response or URL passes minimum WCAG accessibility health score.
     */
    public function assertPageIsAccessible(string $htmlOrUrl, int $minScore = 80): void
    {
        $scanner = new Scanner();
        $result = str_starts_with($htmlOrUrl, 'http')
            ? $scanner->scanUrl($htmlOrUrl)
            : $scanner->scan($htmlOrUrl);

        $score = intval($result['score'] ?? 0);
        $violations = count($result['violations'] ?? []);

        $message = sprintf('Target failed accessibility audit with score %d/100 (%d violations).', $score, $violations);
        
        /** @var \PHPUnit\Framework\TestCase $this */
        $this->assertGreaterThanOrEqual($minScore, $score, $message);
    }
}
