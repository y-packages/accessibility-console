<?php

namespace YakNet\AccessibilityConsole\Tests\Syntax;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Syntax\HtmlSyntaxLinter;

class HtmlSyntaxLinterTest extends TestCase
{
    private HtmlSyntaxLinter $linter;

    protected function setUp(): void
    {
        $this->linter = new HtmlSyntaxLinter();
    }

    public function testDetectsDuplicateAttributes(): void
    {
        $html = '<div><input type="text" id="username" class="form-control" id="duplicate_user"></div>';
        $issues = $this->linter->lint($html);

        $this->assertNotEmpty($issues);
        $duplicateIssues = array_filter($issues, fn($i) => $i->code === 'SYNTAX_DUPLICATE_ATTR');
        $this->assertCount(1, $duplicateIssues);
    }

    public function testDetectsInvalidSelfClosingOnNonVoidElement(): void
    {
        $html = '<div><div class="card" /><p>Text</p></div>';
        $issues = $this->linter->lint($html);

        $this->assertNotEmpty($issues);
        $selfCloseIssues = array_filter($issues, fn($i) => $i->code === 'SYNTAX_INVALID_SELF_CLOSING');
        $this->assertCount(1, $selfCloseIssues);
    }

    public function testDetectsUnclosedComments(): void
    {
        $html = '<div><!-- unclosed comment <p>Text</p></div>';
        $issues = $this->linter->lint($html);

        $this->assertNotEmpty($issues);
        $commentIssues = array_filter($issues, fn($i) => $i->code === 'SYNTAX_UNCLOSED_COMMENT');
        $this->assertCount(1, $commentIssues);
    }

    public function testDetectsMismatchedTags(): void
    {
        $html = '<main><section><p>Text</section></main>';
        $issues = $this->linter->lint($html);

        $this->assertNotEmpty($issues);
        $mismatchIssues = array_filter($issues, fn($i) => in_array($i->code, ['SYNTAX_MISMATCHED_TAG', 'SYNTAX_UNCLOSED_TAG']));
        $this->assertNotEmpty($mismatchIssues);
    }

    public function testPassesValidHtml5(): void
    {
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Test</title></head><body><main><h1>Hello</h1><p>World</p></main></body></html>';
        $issues = $this->linter->lint($html);

        $this->assertEmpty($issues);
    }
}
