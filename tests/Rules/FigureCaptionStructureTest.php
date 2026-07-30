<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\FigureCaptionStructure;

class FigureCaptionStructureTest extends TestCase
{
    public function testFlagsOrphanFigcaption(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FigureCaptionStructure());

        $violations = $scanner->scan('<div><figcaption>Caption text</figcaption></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_3_1_FIGCAPTION_PARENT', $violations[0]->ruleId);
    }

    public function testPassesValidFigcaptionInFigure(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new FigureCaptionStructure());

        $violations = $scanner->scan('<figure><img src="img.jpg" alt="test"><figcaption>Caption text</figcaption></figure>');
        $this->assertCount(0, $violations);
    }
}
