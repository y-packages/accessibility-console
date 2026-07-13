<?php

namespace YakNet\AccessibilityConsole\Core\Metrics;

use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\Severity;

class AccessibilityScoreCalculator
{
    /**
     * Calculate accessibility score (0-100) and user impact summaries.
     *
     * @param Violation[] $violations
     * @return array{score: int, visualImpact: int, motorImpact: int, cognitiveImpact: int}
     */
    public static function calculate(array $violations): array
    {
        $score = 100;
        
        $visualCount = 0;
        $motorCount = 0;
        $cognitiveCount = 0;

        foreach ($violations as $v) {
            // Deduct points based on severity
            $deduction = 3; // Warning default
            if ($v->severity === Severity::CRITICAL) {
                $deduction = 15;
            } elseif ($v->severity === Severity::ERROR) {
                $deduction = 8;
            }
            $score -= $deduction;

            // Map rule ID to disabled user groups impact
            $id = $v->ruleId;
            if (self::isVisual($id)) {
                $visualCount++;
            }
            if (self::isMotor($id)) {
                $motorCount++;
            }
            if (self::isCognitive($id)) {
                $cognitiveCount++;
            }
        }

        $score = max(0, $score);

        // Normalize counts to percentage indicators of severity
        $total = count($violations);
        $visualImpact = $total > 0 ? (int)(($visualCount / $total) * 100) : 0;
        $motorImpact = $total > 0 ? (int)(($motorCount / $total) * 100) : 0;
        $cognitiveImpact = $total > 0 ? (int)(($cognitiveCount / $total) * 100) : 0;

        return [
            'score' => $score,
            'visualImpact' => $visualImpact,
            'motorImpact' => $motorImpact,
            'cognitiveImpact' => $cognitiveImpact,
        ];
    }

    private static function isVisual(string $ruleId): bool
    {
        $visualKeywords = ['ALT', 'LABEL', 'TITLE', 'ARIA', 'LANG', 'CONTRAST', 'COLOR', 'TEXT', 'HEADING'];
        foreach ($visualKeywords as $key) {
            if (str_contains($ruleId, $key)) {
                return true;
            }
        }
        return false;
    }

    private static function isMotor(string $ruleId): bool
    {
        $motorKeywords = ['ACCESSKEY', 'FOCUS', 'TABINDEX', 'KEYBOARD', 'LINK_JAVASCRIPT', 'AUTOFOCUS', 'SCROLLABLE'];
        foreach ($motorKeywords as $key) {
            if (str_contains($ruleId, $key)) {
                return true;
            }
        }
        return false;
    }

    private static function isCognitive(string $ruleId): bool
    {
        $cognitiveKeywords = ['MARQUEE', 'BLINK', 'REFRESH', 'AUTOPLAY', 'AUTOCOMPLETE'];
        foreach ($cognitiveKeywords as $key) {
            if (str_contains($ruleId, $key)) {
                return true;
            }
        }
        return false;
    }
}
