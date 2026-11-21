<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ArticleAccessibleHeading extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_ARTICLE_HEADING';
    }

    public function getDescription(): string
    {
        return '<article> elements should contain a heading (h1-h6 or role="heading") or provide an aria-label / aria-labelledby.';
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
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'article') {
            return null;
        }

        $hasLabel = ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '')
            || ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '');

        if ($hasLabel) {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc !== null) {
            $xpath = new DOMXPath($doc);
            $headings = $xpath->query('.//h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6 | .//*[@role="heading"]', $element);
            if ($headings !== false && $headings->length > 0) {
                return null;
            }
        }

        return $this->createViolation(
            $element,
            '<article> element has no heading (h1-h6) and no aria-label/aria-labelledby to identify its topic.',
            'Include a descriptive heading (e.g., <h2>) inside the <article> or add an aria-label attribute.'
        );
    }
}
