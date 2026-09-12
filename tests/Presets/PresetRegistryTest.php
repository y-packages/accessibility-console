<?php

namespace YakNet\AccessibilityConsole\Tests\Presets;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;
use YakNet\AccessibilityConsole\Presets\Section508Preset;
use YakNet\AccessibilityConsole\Presets\En301549Preset;
use YakNet\AccessibilityConsole\Presets\ECommercePreset;

class PresetRegistryTest extends TestCase
{
    public function testRegistryLoadsDefaultPresets(): void
    {
        $registry = new PresetRegistry();
        $all = $registry->all();

        $this->assertArrayHasKey('section508', $all);
        $this->assertArrayHasKey('en301549', $all);
        $this->assertArrayHasKey('ecommerce', $all);
    }

    public function testGetPresetByName(): void
    {
        $registry = new PresetRegistry();

        $preset = $registry->get('section508');
        $this->assertInstanceOf(Section508Preset::class, $preset);
        $this->assertSame(3, $preset->getLevel());

        $ecommerce = $registry->get('ecommerce');
        $this->assertInstanceOf(ECommercePreset::class, $ecommerce);
        $this->assertContains('WCAG_3_3_7_REDUNDANT_ENTRY', $ecommerce->getIncludedRules());
    }

    public function testGetNonExistentPresetReturnsNull(): void
    {
        $registry = new PresetRegistry();
        $this->assertNull($registry->get('non_existent'));
    }
}
