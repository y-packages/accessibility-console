<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\TrackValidation;

class TrackValidationTest extends TestCase
{
    public function testFlagsInvalidTrackKind(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TrackValidation());

        $violations = $scanner->scan('<video src="v.mp4"><track kind="invalid_kind" src="track.vtt"></video>');
        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_1_2_2_TRACK_VALIDATION', $violations[0]->ruleId);
    }

    public function testFlagsMissingSrclangOnCaptions(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TrackValidation());

        $violations = $scanner->scan('<video src="v.mp4"><track kind="captions" src="track.vtt" label="English"></video>');
        $this->assertCount(1, $violations);
    }

    public function testPassesValidTrack(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new TrackValidation());

        $violations = $scanner->scan('<video src="v.mp4"><track kind="captions" src="track.vtt" srclang="en" label="English Captions"></video>');
        $this->assertCount(0, $violations);
    }
}
