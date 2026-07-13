<?php

namespace YakNet\AccessibilityConsole\Tests\Core;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Baseline\FuzzyBaselineMatcher;

class FuzzyBaselineMatcherTest extends TestCase
{
    public function testFuzzyMatchIgnoresAttributeOrderAndSpaces(): void
    {
        $snippetA = '<img src="logo.png" alt="logo" class="banner">';
        $snippetB = ' <img class="banner"  src="logo.png"   alt="logo"> ';

        $this->assertTrue(FuzzyBaselineMatcher::match($snippetA, $snippetB));
    }

    public function testNormalizeLowercaseTags(): void
    {
        $html = '<IMG SRC="logo.png" ALT="logo">';
        $norm = FuzzyBaselineMatcher::normalize($html);

        $this->assertSame('<img alt="logo" src="logo.png">', $norm);
    }
}
