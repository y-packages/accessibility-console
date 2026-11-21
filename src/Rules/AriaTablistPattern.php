<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaTablistPattern extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_TABLIST';
    }

    public function getDescription(): string
    {
        return 'WAI-ARIA Tabs (tablist/tab/tabpanel) must follow the APG pattern with aria-selected, aria-controls, and aria-labelledby.';
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
        $role = strtolower(trim($element->getAttribute('role')));

        if ($role === 'tab') {
            if (!$element->hasAttribute('aria-selected')) {
                return $this->createViolation(
                    $element,
                    'Element with role="tab" is missing required aria-selected ("true" or "false") attribute.',
                    'Add aria-selected="true" for the active tab and aria-selected="false" for inactive tabs.'
                );
            }

            if (!$element->hasAttribute('aria-controls') || trim($element->getAttribute('aria-controls')) === '') {
                return $this->createViolation(
                    $element,
                    'Element with role="tab" should declare aria-controls referencing its associated role="tabpanel" element.',
                    'Add aria-controls="<panel-id>" pointing to the corresponding tabpanel.'
                );
            }
        }

        if ($role === 'tabpanel') {
            $hasLabel = ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '')
                || ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '');
            if (!$hasLabel) {
                return $this->createViolation(
                    $element,
                    'Element with role="tabpanel" is missing an accessible name (aria-labelledby referencing its tab, or aria-label).',
                    'Add aria-labelledby="<tab-id>" to associate the tabpanel with its controlling tab.'
                );
            }
        }

        if ($role === 'tablist') {
            $doc = $element->ownerDocument;
            if ($doc !== null) {
                $xpath = new DOMXPath($doc);
                $tabs = $xpath->query('.//*[@role="tab"]', $element);
                if ($tabs !== false && $tabs->length === 0) {
                    return $this->createViolation(
                        $element,
                        'Element with role="tablist" does not contain any child elements with role="tab".',
                        'Add interactive elements with role="tab" inside the tablist container.'
                    );
                }
            }
        }

        return null;
    }
}
