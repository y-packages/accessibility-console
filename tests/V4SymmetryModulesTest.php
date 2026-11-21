<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Cache\ScanCache;
use YakNet\AccessibilityConsole\CI\CiWorkflowGenerator;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\BaselineManager;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\ImgAltText;
use YakNet\AccessibilityConsole\Testing\AccessibilityAssertions;

class V4SymmetryModulesTest extends TestCase
{
    use AccessibilityAssertions;

    public function testScanCacheStoresAndServesHits(): void
    {
        $cacheFile = sys_get_temp_dir() . '/yaknet_cache_' . uniqid() . '.json';
        $htmlFile = sys_get_temp_dir() . '/yaknet_cached_page_' . uniqid() . '.html';
        file_put_contents($htmlFile, '<main><img src="a.png"></main>');

        try {
            $scanner = new Scanner();
            $scanner->addRule(new ImgAltText());

            // First run: cache miss & populate
            $cache1 = new ScanCache($cacheFile, 'sig-v1');
            $analyser1 = new Analyser(new Config(), $scanner, new BaselineManager(), null, null, null, $cache1);
            $res1 = $analyser1->analyse([$htmlFile]);
            $this->assertCount(1, $res1['violations']);
            $this->assertSame(0, $cache1->getStats()['hits']);
            $this->assertSame(1, $cache1->getStats()['misses']);

            // Second run: cache hit!
            $cache2 = new ScanCache($cacheFile, 'sig-v1');
            $cache2->load();
            $analyser2 = new Analyser(new Config(), $scanner, new BaselineManager(), null, null, null, $cache2);
            $res2 = $analyser2->analyse([$htmlFile]);
            $this->assertCount(1, $res2['violations']);
            $this->assertSame(1, $cache2->getStats()['hits']);
            $this->assertSame(100.0, $cache2->getStats()['hit_rate']);
        } finally {
            @unlink($cacheFile);
            @unlink($htmlFile);
        }
    }

    public function testCiWorkflowGeneratorSupportsAllFourPlatforms(): void
    {
        $generator = new CiWorkflowGenerator();

        foreach (CiWorkflowGenerator::getSupportedPlatforms() as $platform) {
            $yaml = $generator->generate($platform, 'wcag22-aa', 95, 'templates');
            $this->assertStringContainsString('vendor/bin/a11y', $yaml);
            $this->assertStringContainsString('--preset=wcag22-aa', $yaml);
            $this->assertNotEmpty(CiWorkflowGenerator::getDefaultPath($platform));
        }
    }

    public function testAccessibilityAssertionsTraitValidatesCleanHtml(): void
    {
        $cleanHtml = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>YakNet</title></head><body><main><h1>Welcome</h1><p>Accessible page.</p></main></body></html>';

        $this->assertHtmlAccessible($cleanHtml, level: 4);
        $this->assertAccessibilityScoreAtLeast($cleanHtml, minScore: 95, level: 4);
        $this->assertMeetsAccessibilityPreset($cleanHtml, 'wcag22-aa');
        $this->assertNoAccessibilityRuleViolation($cleanHtml, 'WCAG_1_1_1_ALT');
    }
}
