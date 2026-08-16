<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class DetailsMissingSummary extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_DETAILS_SUMMARY';
    }

    public function getDescription(): string
    {
        return '<details> elements must contain a <summary> element to provide an accessible disclosure label.';
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
        if (strtolower($element->tagName) !== 'details') {
            return null;
        }

        $hasSummary = false;
        $summaryEmpty = false;

        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'summary') {
                $hasSummary = true;
                $text = trim($child->textContent);
                $ariaLabel = trim($child->getAttribute('aria-label'));
                if ($text === '' && $ariaLabel === '') {
                    $summaryEmpty = true;
                }
                break;
            }
        }

        if (!$hasSummary) {
            return $this->createViolation(
                $element,
                "<details> element is missing a <summary> element as its label.",
                "Add a <summary> element with descriptive text as the first child of the <details> tag."
            );
        }

        if ($summaryEmpty) {
            return $this->createViolation(
                $element,
                "<summary> element inside <details> is empty.",
                "Provide meaningful text inside the <summary> element to describe the expandable content."
            );
        }

        return null;
    }
}
