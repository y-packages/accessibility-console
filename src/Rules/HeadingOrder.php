<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HeadingOrder extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_HEADING';
    }

    public function getDescription(): string
    {
        return 'Headings should follow a logical nesting order without skipping levels.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 2;
    }

    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $xpath = new \DOMXPath($doc);
        $headings = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');

        if ($headings === false || $headings->length === 0) {
            return [];
        }

        $lastLevel = 0;

        foreach ($headings as $heading) {
            if (!$heading instanceof DOMElement) {
                continue;
            }

            if (!preg_match('/^h([1-6])$/i', $heading->tagName, $matches)) {
                continue;
            }

            $currentLevel = (int)$matches[1];

            // If heading jumps more than 1 level down (e.g. h1 to h3, h2 to h5)
            if ($lastLevel > 0 && $currentLevel > $lastLevel + 1) {
                $violations[] = $this->createViolation(
                    "Heading level <h{$currentLevel}> jumps from <h{$lastLevel}>, skipping intermediate heading levels.",
                    $heading,
                    "Ensure headings follow a logical sequence (e.g., <h1> followed by <h2>, not skipping to <h{$currentLevel}>)."
                );
            }

            $lastLevel = $currentLevel;
        }

        return $violations;
    }
}
