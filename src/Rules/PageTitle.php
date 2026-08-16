<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class PageTitle extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_4_2_PAGE_TITLE';
    }

    public function getDescription(): string
    {
        return 'Web pages must have a <title> element inside the <head>.';
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
        return 1;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'head') {
            return null;
        }

        $titles = $element->getElementsByTagName('title');
        
        if ($titles->length === 0) {
            return $this->createViolation(
                $element,
                "The <head> element is missing a <title> element.",
                'Add a <title> element inside the <head> to provide a page title.'
            );
        }

        return null;
    }
}
