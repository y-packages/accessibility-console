<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaBrailleEquivalent extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_BRAILLE';
    }

    public function getDescription(): string
    {
        return 'Elements with aria-braillelabel or aria-brailleroledescription must provide a standard accessible equivalent.';
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
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if ($element->hasAttribute('aria-braillelabel')) {
            $hasName = false;
            if ($element->hasAttribute('aria-label') && trim($element->getAttribute('aria-label')) !== '') {
                $hasName = true;
            } elseif ($element->hasAttribute('aria-labelledby') && trim($element->getAttribute('aria-labelledby')) !== '') {
                $hasName = true;
            } elseif ($element->hasAttribute('title') && trim($element->getAttribute('title')) !== '') {
                $hasName = true;
            } elseif ($element->hasAttribute('alt') && trim($element->getAttribute('alt')) !== '') {
                $hasName = true;
            } elseif (trim($element->textContent) !== '') {
                $hasName = true;
            }

            if (!$hasName) {
                return $this->createViolation(
                    $element,
                    "Element has aria-braillelabel but is missing a standard accessible name (aria-label, aria-labelledby, or text content).",
                    "Add a standard aria-label or aria-labelledby in addition to the aria-braillelabel."
                );
            }
        }

        if ($element->hasAttribute('aria-brailleroledescription')) {
            if (!$element->hasAttribute('aria-roledescription') || trim($element->getAttribute('aria-roledescription')) === '') {
                return $this->createViolation(
                    $element,
                    "Element has aria-brailleroledescription but is missing the standard aria-roledescription attribute.",
                    "Add an aria-roledescription attribute to accompany aria-brailleroledescription."
                );
            }
        }

        return null;
    }
}
