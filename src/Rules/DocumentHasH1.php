<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMDocument;
use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\AbstractRule as DocumentAbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class DocumentHasH1 extends DocumentAbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_4_6_DOCUMENT_H1';
    }

    public function getDescription(): string
    {
        return 'Full HTML pages with a <main> landmark or headings should include an <h1> primary heading (WCAG 2.4.6).';
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
        return 3;
    }

    public function check(DOMDocument $doc): array
    {
        $xpath = new DOMXPath($doc);

        // Only evaluate full documents that have a <main> landmark (avoids false positives on small partial snippets)
        $mains = $xpath->query('//main | //*[@role="main"]');
        if ($mains === false || $mains->length === 0) {
            return [];
        }

        $h1s = $xpath->query('//h1 | //*[@role="heading" and @aria-level="1"]');
        if ($h1s !== false && $h1s->length > 0) {
            return [];
        }

        $main = $mains->item(0);
        if ($main instanceof DOMElement) {
            return [
                $this->createViolation(
                    'Document contains a <main> landmark but lacks a primary <h1> heading for screen reader orientation (WCAG 2.4.6).',
                    $main,
                    'Add a single descriptive <h1> heading inside <main> representing the primary topic of the page.'
                ),
            ];
        }

        return [];
    }
}
