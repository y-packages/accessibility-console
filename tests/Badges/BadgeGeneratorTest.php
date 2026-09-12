<?php

namespace YakNet\AccessibilityConsole\Tests\Badges;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Badges\BadgeGenerator;

class BadgeGeneratorTest extends TestCase
{
    public function testGeneratesValidScoreBadgeSvg(): void
    {
        $svg = BadgeGenerator::generateScoreBadge(95);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString('accessibility', $svg);
        $this->assertStringContainsString('95/100', $svg);
        $this->assertStringContainsString('#10b981', $svg); // green color for >= 90
    }

    public function testGeneratesWarningScoreBadgeSvg(): void
    {
        $svg = BadgeGenerator::generateScoreBadge(65);

        $this->assertStringContainsString('65/100', $svg);
        $this->assertStringContainsString('#ef4444', $svg); // red color for < 70
    }

    public function testGeneratesComplianceBadge(): void
    {
        $svg = BadgeGenerator::generateComplianceBadge('WCAG 2.1 AA', true);

        $this->assertStringContainsString('WCAG 2.1 AA', $svg);
        $this->assertStringContainsString('passed', $svg);
    }

    public function testGeneratesViolationsBadge(): void
    {
        $svg = BadgeGenerator::generateViolationsBadge(0);
        $this->assertStringContainsString('0 issues', $svg);

        $svgIssues = BadgeGenerator::generateViolationsBadge(4);
        $this->assertStringContainsString('4 issues', $svgIssues);
    }

    public function testGeneratesBadgeWithCustomOptions(): void
    {
        $options = new \YakNet\AccessibilityConsole\Badges\BadgeOptions(
            label: 'custom-a11y',
            leftColor: '#003366',
            rightColor: '#00cc66',
            borderRadius: 6
        );

        $svg = BadgeGenerator::generateScoreBadge(88, $options);
        $this->assertStringContainsString('custom-a11y', $svg);
        $this->assertStringContainsString('#003366', $svg);
        $this->assertStringContainsString('#00cc66', $svg);
        $this->assertStringContainsString('rx="6"', $svg);
    }
}
