<?php

namespace YakNet\AccessibilityConsole\Tests\AutoFix;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\SelfHealing\LocalHealer;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LocalHealerTest extends TestCase
{
    private LocalHealer $healer;

    protected function setUp(): void
    {
        $this->healer = new LocalHealer();
    }

    public function testHealsMissingImgAlt(): void
    {
        $violation = new Violation(
            'WCAG_1_1_1_IMG_ALT',
            'Image missing alt',
            Severity::ERROR,
            WCAGStandard::A,
            '<img src="photo.jpg">'
        );

        $healed = $this->healer->heal($violation);
        $this->assertNotNull($healed);
        $this->assertStringContainsString('FIX: <img alt="" src="photo.jpg">', $healed);
    }

    public function testHealsDecorativeSvg(): void
    {
        $violation = new Violation(
            'WCAG_1_1_1_SVG',
            'SVG needs accessible text',
            Severity::ERROR,
            WCAGStandard::A,
            '<svg class="icon"><path d="..."/></svg>'
        );

        $healed = $this->healer->heal($violation);
        $this->assertNotNull($healed);
        $this->assertStringContainsString('FIX: <svg aria-hidden="true" class="icon">', $healed);
    }

    public function testHealsTargetBlankLinkRel(): void
    {
        $violation = new Violation(
            'WCAG_LINK_TARGET_BLANK',
            'Target blank missing noopener',
            Severity::WARNING,
            WCAGStandard::A,
            '<a href="https://example.com" target="_blank">External</a>'
        );

        $healed = $this->healer->heal($violation);
        $this->assertNotNull($healed);
        $this->assertStringContainsString('rel="noopener noreferrer"', $healed);
    }

    public function testHealsPositiveTabindex(): void
    {
        $violation = new Violation(
            'WCAG_2_4_3_TABINDEX',
            'Avoid positive tabindex',
            Severity::WARNING,
            WCAGStandard::A,
            '<button tabindex="5">Submit</button>'
        );

        $healed = $this->healer->heal($violation);
        $this->assertNotNull($healed);
        $this->assertStringContainsString('tabindex="0"', $healed);
    }

    public function testHealsInvalidTableScope(): void
    {
        $violation = new Violation(
            'WCAG_1_3_1_TABLE_SCOPE',
            'Scope only on headers',
            Severity::ERROR,
            WCAGStandard::A,
            '<td scope="col">Data cell</td>'
        );

        $healed = $this->healer->heal($violation);
        $this->assertNotNull($healed);
        $this->assertStringNotContainsString('scope=', $healed);
    }

    public function testHealsMetaRefresh(): void
    {
        $violation = new Violation(
            'WCAG_2_2_1_META_REFRESH',
            'Meta refresh banned',
            Severity::ERROR,
            WCAGStandard::A,
            '<meta http-equiv="refresh" content="30">'
        );

        $healed = $this->healer->heal($violation);
        $this->assertNotNull($healed);
        $this->assertStringContainsString('<!-- <meta http-equiv="refresh" content="30"> (Removed', $healed);
    }
}
