<?php

namespace YakNet\AccessibilityConsole\Rules;

use Symfony\Component\DomCrawler\Crawler;
use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class BannedSelectors extends AbstractRule
{
    /** @var array<string, string> */
    private array $bannedSelectors = [
        'center' => 'Obsolete <center> tag. Use CSS text-align instead.',
        'blink' => 'Obsolete <blink> tag. Avoid blinking/flashing content.',
        'marquee' => 'Obsolete <marquee> tag. Avoid moving content.',
        'font' => 'Obsolete <font> tag. Use CSS font styling instead.',
        '*[align]' => 'Deprecated align attribute. Use CSS text-align or float instead.',
        '*[bgcolor]' => 'Deprecated bgcolor attribute. Use CSS background-color instead.',
    ];

    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $crawler = new Crawler($doc);

        foreach ($this->bannedSelectors as $selector => $message) {
            try {
                $nodes = $crawler->filter($selector);
                foreach ($nodes as $node) {
                    if ($node instanceof \DOMElement) {
                        $violations[] = $this->createViolation(
                            sprintf('[Banned Selector: "%s"] %s', $selector, $message),
                            $node
                        );
                    }
                }
            } catch (\Throwable $e) {
                // Skip invalid selector configurations
            }
        }

        return $violations;
    }

    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }
}
