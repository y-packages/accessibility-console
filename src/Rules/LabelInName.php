<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LabelInName extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_5_3_LABEL_IN_NAME';
    }

    public function getDescription(): string
    {
        return 'Accessible name must contain the visible text label of interactive controls (WCAG 2.5.3).';
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
        $role = strtolower($element->getAttribute('role'));
        $isInteractive = in_array($tag, ['button', 'a'], true)
            || ($tag === 'input' && in_array(strtolower($element->getAttribute('type')), ['button', 'submit', 'reset'], true))
            || in_array($role, ['button', 'link'], true);

        if (!$isInteractive) {
            return null;
        }

        // Avoid template false positives
        $html = $element->ownerDocument ? $element->ownerDocument->saveHTML($element) : '';
        if ($html !== false && preg_match('/<\?php|\{\{|\{%/i', $html)) {
            return null;
        }

        // Determine visible text
        $visibleText = '';
        if ($tag === 'input') {
            $visibleText = trim($element->getAttribute('value'));
        } else {
            $visibleText = trim($element->textContent);
        }

        // If there is no visible text (e.g. icon-only button), Label in Name criterion does not apply
        if ($visibleText === '' || mb_strlen($visibleText) < 2) {
            return null;
        }

        // Get explicit accessible name
        $accessibleName = '';
        if ($element->hasAttribute('aria-label')) {
            $accessibleName = trim($element->getAttribute('aria-label'));
        } elseif ($element->hasAttribute('aria-labelledby')) {
            $id = trim($element->getAttribute('aria-labelledby'));
            $doc = $element->ownerDocument;
            if ($doc !== null && $id !== '') {
                $xpath = new DOMXPath($doc);
                $nodes = $xpath->query('//*[@id="' . $id . '"]');
                if ($nodes !== false && $nodes->length > 0) {
                    $node = $nodes->item(0);
                    if ($node instanceof DOMNode) {
                        $accessibleName = trim($node->textContent);
                    }
                }
            }
        }

        // If no explicit ARIA name is set, the visible text itself is the accessible name
        if ($accessibleName === '') {
            return null;
        }

        // Normalization: clean extra whitespace and lower case
        $normVisible = mb_strtolower((string)preg_replace('/\s+/', ' ', $visibleText));
        $normAcc = mb_strtolower((string)preg_replace('/\s+/', ' ', $accessibleName));

        // WCAG 2.5.3: Accessible name must contain the visible text
        if (!str_contains($normAcc, $normVisible)) {
            return $this->createViolation(
                $element,
                sprintf(
                    "Accessible name \"%s\" does not contain the visible text \"%s\".",
                    $accessibleName,
                    $visibleText
                ),
                sprintf(
                    "Update aria-label to include \"%s\" (e.g. aria-label=\"%s %s\") so speech input users can activate the control.",
                    $visibleText,
                    $visibleText,
                    $accessibleName
                )
            );
        }

        return null;
    }
}
