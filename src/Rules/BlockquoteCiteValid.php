<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class BlockquoteCiteValid extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_BLOCKQUOTE_MISUSE';
    }

    public function getDescription(): string
    {
        return '<blockquote> elements must not have an empty cite attribute and should only be used for actual quotations, not visual indentation.';
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
        if (strtolower($element->tagName) !== 'blockquote') {
            return null;
        }

        if ($element->hasAttribute('cite') && trim($element->getAttribute('cite')) === '') {
            return $this->createViolation(
                $element,
                '<blockquote> element has an empty cite="" attribute.',
                'Provide a valid source URL in the cite attribute or remove the empty attribute.'
            );
        }

        $text = trim($element->textContent);
        if ($text === '') {
            return $this->createViolation(
                $element,
                '<blockquote> element contains no readable quotation text and appears to be used for layout spacing.',
                'Remove empty <blockquote> elements and use CSS margin/padding for indentation.'
            );
        }

        return null;
    }
}
