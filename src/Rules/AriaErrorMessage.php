<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaErrorMessage extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_3_1_ARIA_ERRORMESSAGE';
    }

    public function getDescription(): string
    {
        return 'aria-errormessage must reference an existing element ID and the element must have aria-invalid set.';
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
        if (!$element->hasAttribute('aria-errormessage')) {
            return null;
        }

        $targetId = trim($element->getAttribute('aria-errormessage'));
        if ($targetId === '') {
            return $this->createViolation(
                $element,
                "Attribute aria-errormessage is empty.",
                "Provide the ID of an element containing the error message text."
            );
        }

        // 1. Verify that target element exists in document
        $doc = $element->ownerDocument;
        if ($doc !== null) {
            $xpath = new \DOMXPath($doc);
            $targets = $xpath->query('//*[@id="' . $targetId . '"]');
            if ($targets === false || $targets->length === 0) {
                return $this->createViolation(
                    $element,
                    "Attribute aria-errormessage=\"{$targetId}\" references an ID that does not exist in the document.",
                    "Ensure the referenced error message element ID exists in the HTML document."
                );
            }
        }

        // 2. Check if element has aria-invalid set to a truthy value (true, grammar, spelling)
        $ariaInvalid = strtolower(trim($element->getAttribute('aria-invalid')));
        if ($ariaInvalid === '' || $ariaInvalid === 'false') {
            return $this->createViolation(
                $element,
                "Element uses aria-errormessage=\"{$targetId}\" but does not have aria-invalid set to \"true\".",
                "Add aria-invalid=\"true\" to the control when an error message is active."
            );
        }

        return null;
    }
}
