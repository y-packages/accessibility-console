<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\LabelInName;

class LabelInNameTest extends TestCase
{
    public function testFlagsButtonWhoseAriaLabelDoesNotContainVisibleText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new LabelInName());

        $html = '<button aria-label="Send Form">Submit</button>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_5_3_LABEL_IN_NAME', $violations[0]->ruleId);
        $this->assertStringContainsString('Submit', $violations[0]->message);
    }

    public function testPassesButtonWhoseAriaLabelContainsVisibleText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new LabelInName());

        $html = '<button aria-label="Submit this inquiry">Submit</button>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }

    public function testPassesButtonWithoutExplicitAriaLabel(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new LabelInName());

        $html = '<button>Click Here</button>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }

    public function testFlagsLinkWhoseAriaLabelDiffersFromVisibleText(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new LabelInName());

        $html = '<a href="/contact" aria-label="Reach out to support">Contact Us</a>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
    }
}
