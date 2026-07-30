<?php

namespace YakNet\AccessibilityConsole\Audit;

use YakNet\AccessibilityConsole\Core\Scanner;

class SiteCrawler
{
    private Scanner $scanner;

    public function __construct(?Scanner $scanner = null)
    {
        $this->scanner = $scanner ?? new Scanner();
    }

    /**
     * Crawl and scan multiple pages of a website up to a maximum page limit.
     *
     * @param string $baseUrl Base URL to start crawling
     * @param int $maxPages Maximum number of pages to audit
     * @return array<string, mixed> Aggregated site-wide audit results
     */
    public function crawlAndAudit(string $baseUrl, int $maxPages = 5): array
    {
        /** @var array<string, bool> $visited */
        $visited = [];
        $queue = [$baseUrl];
        /** @var array<string, mixed> $results */
        $results = [];

        while (!empty($queue) && count($visited) < $maxPages) {
            $url = array_shift($queue);
            if (empty($url) || isset($visited[$url])) {
                continue;
            }

            $visited[$url] = true;
            try {
                $html = @file_get_contents($url);
                $violations = $html !== false ? $this->scanner->scan($html) : [];
                $score = max(0, 100 - count($violations) * 3);

                $results[$url] = [
                    'score' => $score,
                    'violations_count' => count($violations),
                    'violations' => $violations,
                ];
            } catch (\Throwable $e) {
                $results[$url] = ['error' => $e->getMessage(), 'score' => 0];
            }
        }

        $scores = [];
        $totalViolations = 0;
        foreach ($results as $res) {
            if (is_array($res)) {
                if (isset($res['score']) && is_numeric($res['score'])) {
                    $scores[] = intval($res['score']);
                }
                if (isset($res['violations_count']) && is_numeric($res['violations_count'])) {
                    $totalViolations += intval($res['violations_count']);
                }
            }
        }

        $avgScore = count($scores) > 0 ? (int)round(array_sum($scores) / count($scores)) : 0;

        return [
            'base_url' => $baseUrl,
            'pages_audited' => count($visited),
            'average_score' => $avgScore,
            'total_violations' => $totalViolations,
            'page_results' => $results,
        ];
    }
}
