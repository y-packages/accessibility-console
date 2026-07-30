<?php

namespace YakNet\AccessibilityConsole\Benchmarks;

use YakNet\AccessibilityConsole\Core\Scanner;

class BenchmarkEngine
{
    private Scanner $scanner;

    public function __construct(?Scanner $scanner = null)
    {
        $this->scanner = $scanner ?? new Scanner();
    }

    /**
     * Compare accessibility health scores across multiple target URLs.
     *
     * @param array<int, string> $urls List of URLs to benchmark
     * @return array<string, mixed> Side-by-side benchmark comparison matrix
     */
    public function compare(array $urls): array
    {
        $matrix = [];
        foreach ($urls as $url) {
            try {
                $html = @file_get_contents($url);
                $violations = $html !== false ? $this->scanner->scan($html) : [];
                $score = max(0, 100 - count($violations) * 3);
                $violationsCount = count($violations);

                $matrix[$url] = [
                    'score' => $score,
                    'violations_count' => $violationsCount,
                    'status' => ($score >= 80) ? 'PASS' : 'FAIL',
                ];
            } catch (\Throwable $e) {
                $matrix[$url] = ['score' => 0, 'violations_count' => -1, 'status' => 'ERROR', 'error' => $e->getMessage()];
            }
        }

        // Rank by score descending
        uasort($matrix, function (array $a, array $b) {
            return intval($b['score']) <=> intval($a['score']);
        });

        return [
            'benchmark_date' => date('Y-m-d H:i:s'),
            'targets_count' => count($urls),
            'rankings' => $matrix,
        ];
    }
}
