<?php

namespace YakNet\AccessibilityConsole\Core\Diff;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class VisualDiffGenerator
{
    private Differ $differ;

    public function __construct()
    {
        $this->differ = new Differ(new UnifiedDiffOutputBuilder("--- Original\n+++ Healed\n"));
    }

    /**
     * Generate a colorized git-style unified diff string.
     *
     * @param string $original
     * @param string $fixed
     * @return string
     */
    public function generate(string $original, string $fixed): string
    {
        $diff = $this->differ->diff($original, $fixed);
        $lines = explode("\n", $diff);
        
        $colorized = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, '+') && !str_starts_with($line, '+++')) {
                $colorized[] = "<fg=green>" . htmlspecialchars($line) . "</>";
            } elseif (str_starts_with($line, '-') && !str_starts_with($line, '---')) {
                $colorized[] = "<fg=red>" . htmlspecialchars($line) . "</>";
            } elseif (str_starts_with($line, '@@')) {
                $colorized[] = "<fg=cyan>" . htmlspecialchars($line) . "</>";
            } elseif (str_starts_with($line, '---') || str_starts_with($line, '+++')) {
                $colorized[] = "<fg=gray>" . htmlspecialchars($line) . "</>";
            } else {
                $colorized[] = htmlspecialchars($line);
            }
        }

        return implode("\n", $colorized);
    }
}
