<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HeaderMainNestedInvalid extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_LANDMARK_NESTING';
    }

    public function getDescription(): string
    {
        return 'The <main> landmark must not be nested inside <article>, <aside>, <footer>, <header>, or <nav> elements.';
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
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        $role = strtolower(trim($element->getAttribute('role')));

        if ($tag !== 'main' && $role !== 'main') {
            return null;
        }

        $forbiddenAncestors = ['article', 'aside', 'footer', 'header', 'nav'];
        $parent = $element->parentNode;

        while ($parent instanceof DOMElement) {
            $parentTag = strtolower($parent->tagName);
            if (in_array($parentTag, $forbiddenAncestors, true)) {
                return $this->createViolation(
                    $element,
                    sprintf('The <main> landmark is improperly nested inside a <%s> element, violating HTML5 landmark rules.', $parentTag),
                    'Move <main> to be a top-level landmark directly inside <body> (or inside a generic <div> wrapper).'
                );
            }
            $parent = $parent->parentNode;
        }

        return null;
    }
}
