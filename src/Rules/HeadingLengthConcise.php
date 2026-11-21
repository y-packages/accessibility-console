<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HeadingLengthConcise extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_4_6_HEADING_LENGTH';
    }

    public function getDescription(): string
    {
        return 'Headings (h1-h6) should be concise (under 160 characters) rather than wrapping full paragraphs (WCAG 2.4.6).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
            return null;
        }

        $text = trim((string)preg_replace('/\s+/', ' ', $element->textContent));
        $length = mb_strlen($text);

        if ($length > 160) {
            return $this->createViolation(
                $element,
                sprintf('Heading <%s> is excessively long (%d characters; recommended <= 160), making screen reader navigation difficult (WCAG 2.4.6).', $tag, $length),
                'Shorten the heading to a concise summary and move explanatory text into a <p> element below it.'
            );
        }

        return null;
    }
}
