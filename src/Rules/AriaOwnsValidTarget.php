<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaOwnsValidTarget extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_OWNS';
    }

    public function getDescription(): string
    {
        return 'The aria-owns attribute must reference existing element IDs in the document and must not reference its own element ID.';
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
        if (!$element->hasAttribute('aria-owns')) {
            return null;
        }

        $owns = trim($element->getAttribute('aria-owns'));
        if ($owns === '' || preg_match('/\{\{|\{%|<\?php/', $owns)) {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        $selfId = trim($element->getAttribute('id'));
        $xpath = new DOMXPath($doc);
        $ids = preg_split('/\s+/', $owns);
        if (!is_array($ids)) {
            return null;
        }

        foreach ($ids as $targetId) {
            if ($targetId === '') {
                continue;
            }
            if ($selfId !== '' && $targetId === $selfId) {
                return $this->createViolation(
                    $element,
                    sprintf('Element creates an aria-owns self-reference cycle pointing to its own id="%s".', $selfId),
                    'Remove the self-referencing ID from aria-owns.'
                );
            }

            if (preg_match('/^[a-zA-Z0-9_\-:.]+$/', $targetId)) {
                $found = $xpath->query('//*[@id="' . $targetId . '"]');
                if ($found !== false && $found->length === 0) {
                    return $this->createViolation(
                        $element,
                        sprintf('Attribute aria-owns references non-existent element ID "%s".', $targetId),
                        sprintf('Ensure an element with id="%s" exists in the DOM or update aria-owns.', $targetId)
                    );
                }
            }
        }

        return null;
    }
}
