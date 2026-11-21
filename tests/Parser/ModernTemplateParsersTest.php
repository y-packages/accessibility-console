<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Parser;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Parser\JsxParser;
use YakNet\AccessibilityConsole\Parser\TemplatePreprocessor;
use YakNet\AccessibilityConsole\Parser\VueParser;
use YakNet\AccessibilityConsole\Rules\ClickHandlerKeyboard;
use YakNet\AccessibilityConsole\Rules\ImgAltText;

class ModernTemplateParsersTest extends TestCase
{
    public function testVueParserExtractsTemplateAndPreservesLineNumbers(): void
    {
        $vue = <<<'VUE'
<script setup>
const title = "YakNet";
</script>
<template>
  <div @click="openModal()">
    <img src="banner.png">
    <span>{{ title }}</span>
  </div>
</template>
VUE;

        $parser = new VueParser();
        $html = $parser->parse($vue);

        $this->assertStringContainsString('onclick="openModal()"', $html);
        $this->assertStringContainsString('VueExpression', $html);

        $scanner = new Scanner();
        $scanner->addRule(new ImgAltText());
        $scanner->addRule(new ClickHandlerKeyboard());
        $violations = $scanner->scan($html);

        $ruleIds = array_map(static fn($v): string => $v->ruleId, $violations);
        $this->assertContains('WCAG_1_1_1_ALT', $ruleIds);
        $this->assertContains('WCAG_2_1_1_CLICK_KEYBOARD', $ruleIds);
    }

    public function testJsxParserNormalizesReactAttributes(): void
    {
        $tsx = <<<'TSX'
export function Card() {
  return (
    <div className="card" onClick={() => handleClick()}>
      <img src="cover.jpg" />
      <label htmlFor="email">Email</label>
    </div>
  );
}
TSX;

        $preprocessor = new TemplatePreprocessor();
        $html = $preprocessor->preprocess($tsx, 'Card.tsx');

        $this->assertStringContainsString('class="card"', $html);
        $this->assertStringContainsString('for="email"', $html);
        $this->assertStringContainsString('onclick="jsxHandler()"', $html);

        $scanner = new Scanner();
        $scanner->addRule(new ImgAltText());
        $violations = $scanner->scan($html);
        $this->assertCount(1, $violations);
    }
}
