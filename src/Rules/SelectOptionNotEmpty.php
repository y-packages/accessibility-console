<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SelectOptionNotEmpty extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_OPTION_EMPTY';
    }

    public function getDescription(): string
    {
        return '<option> elements inside <select> must have non-empty text or a label attribute.';
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
        if (strtolower($element->tagName) !== 'option') {
            return null;
        }

        // Skip <option> inside <datalist> where value attribute acts as label
        $parent = $element->parentNode;
        if ($parent instanceof DOMElement && strtolower($parent->tagName) === 'datalist') {
            return null;
        }

        $text = trim($element->textContent);
        $label = trim($element->getAttribute('label'));

        if ($text === '' && $label === '') {
            return $this->createViolation(
                $element,
                '<option> element has no visible text content and no label attribute.',
                'Provide descriptive text inside <option>...</option> (e.g., <option value="">Select an option</option>).'
            );
        }

        return null;
    }
}
