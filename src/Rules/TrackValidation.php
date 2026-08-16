<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TrackValidation extends AbstractRule
{
    /** @var array<int, string> */
    private static array $validKinds = ['subtitles', 'captions', 'descriptions', 'chapters', 'metadata'];

    public function getId(): string
    {
        return 'WCAG_1_2_2_TRACK_VALIDATION';
    }

    public function getDescription(): string
    {
        return '<track> elements must specify a valid kind, and captions/subtitles must include srclang and label.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'track') {
            return null;
        }

        // 1. Kind attribute validity
        $kind = strtolower(trim($element->getAttribute('kind')));
        if ($kind !== '' && !in_array($kind, self::$validKinds, true)) {
            $validList = implode(', ', self::$validKinds);
            return $this->createViolation(
                $element,
                "Invalid track kind \"{$kind}\". Valid values are: {$validList}.",
                "Set kind to one of: subtitles, captions, descriptions, chapters, metadata."
            );
        }

        // Default kind if omitted is 'subtitles'
        $effectiveKind = $kind !== '' ? $kind : 'subtitles';

        // 2. If captions or subtitles, check srclang and label
        if (in_array($effectiveKind, ['captions', 'subtitles'], true)) {
            $srclang = trim($element->getAttribute('srclang'));
            $label = trim($element->getAttribute('label'));

            if ($srclang === '') {
                return $this->createViolation(
                    $element,
                    "<track kind=\"{$effectiveKind}\"> element is missing the required 'srclang' attribute.",
                    "Add a valid BCP 47 language code (e.g. srclang=\"en\" or srclang=\"tr\")."
                );
            }

            if ($label === '') {
                return $this->createViolation(
                    $element,
                    "<track kind=\"{$effectiveKind}\"> element is missing the required 'label' attribute.",
                    "Add a human-readable title (e.g. label=\"English Captions\" or label=\"Türkçe Altyazı\")."
                );
            }
        }

        return null;
    }
}
