<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\MediaControls;

class MediaControlsTest extends TestCase
{
    public function testFlagsAudioWithoutControls(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new MediaControls());

        $violations = $scanner->scan('<div><audio src="audio.mp3"></audio></div>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_4_2_MEDIA_CONTROLS', $violations[0]->ruleId);
    }

    public function testPassesAudioWithControls(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new MediaControls());

        $violations = $scanner->scan('<div><audio src="audio.mp3" controls></audio></div>');
        $this->assertCount(0, $violations);
    }

    public function testPassesBackgroundMutedAutoplayVideo(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new MediaControls());

        $violations = $scanner->scan('<div><video src="bg.mp4" muted autoplay loop></video></div>');
        $this->assertCount(0, $violations);
    }
}
