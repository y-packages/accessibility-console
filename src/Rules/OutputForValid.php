<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class OutputForValid extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_OUTPUT_FOR';
    }

    public function getDescription(): string
    {
        return '<output> elements with a "for" attribute must reference existing element IDs in the document.';
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
        if (strtolower($element->tagName) !== 'output' || !$element->hasAttribute('for')) {
            return null;
        }

        $forAttr = trim($element->getAttribute('for'));
        if ($forAttr === '' || preg_match('/\{\{|\{%|<\?php/', $forAttr)) {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        $xpath = new DOMXPath($doc);
        $ids = preg_split('/\s+/', $forAttr);
        if (!is_array($ids)) {
            return null;
        }

        foreach ($ids as $targetId) {
            if ($targetId === '' || !preg_match('/^[a-zA-Z0-9_\-:.]+$/', $targetId)) {
                continue;
            }
            $nodes = $xpath->query('//*[@id="' . $targetId . '"]');
            if ($nodes !== false && $nodes->length === 0) {
                return $this->createViolation(
                    $element,
                    sprintf('<output> attribute for="%s" references a non-existent element ID "%s".', $forAttr, $targetId),
                    sprintf('Ensure an input or control with id="%s" exists in the document.', $targetId)
                );
            }
        }

        return null;
    }
}
