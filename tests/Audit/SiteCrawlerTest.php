<?php

namespace YakNet\AccessibilityConsole\Tests\Audit;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Audit\SiteCrawler;

class SiteCrawlerTest extends TestCase
{
    public function testCrawlsBaseUrl(): void
    {
        $crawler = new SiteCrawler();
        $result = $crawler->crawlAndAudit('https://yak.net.tr', 1);

        $this->assertSame('https://yak.net.tr', $result['base_url']);
        $this->assertSame(1, $result['pages_audited']);
        $this->assertArrayHasKey('average_score', $result);
    }
}
