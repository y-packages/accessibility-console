<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SelectValidChildren extends AbstractRule
{
    /** @var array<int, string> */
    private static array $validChildTags = ['option', 'optgroup', 'script', 'template', 'hr'];

    public function getId(): string
    {
        return 'WCAG_1_3_1_SELECT_CHILDREN';
    }

    public function getDescription(): string
    {
        return '<select> elements must only contain valid child elements (<option>, <optgroup>, <script>, <template>).';
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
        if (strtolower($element->tagName) !== 'select') {
            return null;
        }

        $invalidTags = [];

        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $childTag = strtolower($child->tagName);
                if (!in_array($childTag, self::$validChildTags, true)) {
                    $invalidTags[] = "<{$childTag}>";
                }
            }
        }

        if (!empty($invalidTags)) {
            $invalidStr = implode(', ', array_unique($invalidTags));
            return $this->createViolation(
                $element,
                "<select> contains invalid direct child elements: {$invalidStr}.",
                "Remove non-option elements from the <select> or restructure using <optgroup> and <option> tags."
            );
        }

        return null;
    }
}
