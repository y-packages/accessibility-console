<?php

namespace YakNet\AccessibilityConsole\Tests\Core;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Diff\VisualDiffGenerator;

class VisualDiffGeneratorTest extends TestCase
{
    public function testGeneratesVisualDiff(): void
    {
        $generator = new VisualDiffGenerator();
        $original = "hello world\nfoo\n";
        $fixed = "hello world\nbar\n";

        $diff = $generator->generate($original, $fixed);

        $this->assertStringContainsString('--- Original', $diff);
        $this->assertStringContainsString('+++ Healed', $diff);
        $this->assertStringContainsString('<fg=red>-foo', $diff);
        $this->assertStringContainsString('<fg=green>+bar', $diff);
    }
}
