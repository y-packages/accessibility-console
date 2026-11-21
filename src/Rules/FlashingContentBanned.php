<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FlashingContentBanned extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_3_1_FLASHING_CONTENT';
    }

    public function getDescription(): string
    {
        return 'Content must not flash more than three times per second (> 3Hz), which can trigger photosensitive seizures (WCAG 2.3.1).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::CRITICAL;
    }

    public function getLevel(): int
    {
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        $cssText = ($tag === 'style') ? $element->textContent : $element->getAttribute('style');

        if (trim($cssText) === '') {
            return null;
        }

        // Check infinite rapid animations faster than 0.33s (330ms) => > 3 flashes/sec
        if (preg_match('/animation(?:-duration)?\s*:\s*[^;]*\b(0?\.\d+s|\d+ms)\b[^;]*infinite/i', $cssText, $m)
            || (preg_match('/animation-iteration-count\s*:\s*infinite/i', $cssText)
                && preg_match('/animation-duration\s*:\s*(0?\.\d+s|\d+ms)/i', $cssText, $m))) {
            $rawDuration = strtolower($m[1]);
            $ms = str_ends_with($rawDuration, 'ms')
                ? (float)substr($rawDuration, 0, -2)
                : ((float)substr($rawDuration, 0, -1) * 1000.0);

            if ($ms > 0.0 && $ms < 333.0) {
                return $this->createViolation(
                    $element,
                    sprintf('Infinite CSS animation with duration %s exceeds 3 cycles/second, posing a photosensitive seizure hazard (WCAG 2.3.1).', $rawDuration),
                    'Increase animation duration to at least 0.5s (500ms) or avoid rapid flashing/strobing animations.'
                );
            }
        }

        return null;
    }
}
