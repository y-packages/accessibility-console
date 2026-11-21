<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Presets;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;

class PresetsExpandedTest extends TestCase
{
    public function testAllElevenPresetsAreRegistered(): void
    {
        $registry = new PresetRegistry();
        $all = $registry->all();

        $this->assertCount(11, $all);
        foreach (['section508', 'en301549', 'ecommerce', 'wcag22-aa', 'wcag22-aaa', 'eaa2025', 'bitv2', 'government', 'healthcare', 'banking', 'education'] as $name) {
            $this->assertNotNull($registry->get($name), "Expected preset '{$name}' to be registered.");
        }
    }

    public function testGlobalHelpersWorkEndToEnd(): void
    {
        $html = '<html lang="en"><head><title>App</title></head><body><main><h1>Hi</h1><img src="x.png"></main></body></html>';

        $violations = \a11y_scan_preset($html, 'government');
        $this->assertNotEmpty($violations);

        $vpat = \a11y_vpat($html, 'markdown', 'YakNet Portal');
        $this->assertStringContainsString('VPAT 2.5', $vpat);
        $this->assertStringContainsString('YakNet Portal', $vpat);

        $matrix = \a11y_compliance_matrix($html);
        $this->assertArrayHasKey('overall_compliance_percentage', $matrix);
        $this->assertArrayHasKey('principles', $matrix);
    }

    public function testExpandedLocalHealerFixesNewRulesDeterministically(): void
    {
        $healer = new LocalHealer();

        $btnFix = $healer->heal(new Violation('WCAG_3_2_2_BUTTON_TYPE', 'Missing type', Severity::WARNING, WCAGStandard::A, '<button>Click</button>'));
        $this->assertNotNull($btnFix);
        $this->assertStringContainsString('<button type="button">Click</button>', $btnFix);

        $svgFix = $healer->heal(new Violation('WCAG_1_1_1_SVG_FOCUSABLE', 'Missing focusable', Severity::WARNING, WCAGStandard::A, '<svg aria-hidden="true"></svg>'));
        $this->assertNotNull($svgFix);
        $this->assertStringContainsString('focusable="false"', $svgFix);

        $modalFix = $healer->heal(new Violation('WCAG_4_1_2_ARIA_MODAL', 'Missing aria-modal', Severity::WARNING, WCAGStandard::A, '<div role="dialog" aria-label="Info">Hi</div>'));
        $this->assertNotNull($modalFix);
        $this->assertStringContainsString('aria-modal="true"', $modalFix);

        $this->assertGreaterThanOrEqual(30, count(LocalHealer::getSupportedRuleIds()));
    }
}
