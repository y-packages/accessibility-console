<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class RedundantEntry extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_3_7_REDUNDANT_ENTRY';
    }

    public function getDescription(): string
    {
        return 'Forms with repeated information (such as billing and shipping) must provide auto-population or a selection mechanism (WCAG 2.2 SC 3.3.7).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'form') {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        $xpath = new DOMXPath($doc);

        // Find all input and select elements in the form
        $inputs = $xpath->query('.//input | .//select | .//textarea', $element);
        if ($inputs === false || $inputs->length < 4) {
            return null;
        }

        $hasShipping = false;
        $hasBilling = false;
        $hasSameCheckbox = false;

        foreach ($inputs as $input) {
            if (!$input instanceof DOMElement) {
                continue;
            }

            $name = strtolower($input->getAttribute('name'));
            $id = strtolower($input->getAttribute('id'));
            $autocomplete = strtolower($input->getAttribute('autocomplete'));
            $type = strtolower($input->getAttribute('type'));

            if (str_contains($name, 'shipping') || str_contains($id, 'shipping') || str_contains($autocomplete, 'shipping')) {
                $hasShipping = true;
            }

            if (str_contains($name, 'billing') || str_contains($id, 'billing') || str_contains($autocomplete, 'billing')) {
                $hasBilling = true;
            }

            if ($type === 'checkbox') {
                if (str_contains($name, 'same') || str_contains($id, 'same') || str_contains($name, 'copy') || str_contains($id, 'copy')) {
                    $hasSameCheckbox = true;
                }
            }
        }

        // Also check labels for "same as"
        if (!$hasSameCheckbox) {
            $labels = $xpath->query('.//label', $element);
            if ($labels !== false) {
                foreach ($labels as $label) {
                    if ($label instanceof DOMElement) {
                        $labelText = strtolower(trim($label->textContent));
                        if (str_contains($labelText, 'same as') || str_contains($labelText, 'aynı') || str_contains($labelText, 'kopyala')) {
                            $hasSameCheckbox = true;
                            break;
                        }
                    }
                }
            }
        }

        // If form has both shipping and billing fields but no "same as" checkbox
        if ($hasShipping && $hasBilling && !$hasSameCheckbox) {
            return $this->createViolation(
                $element,
                "Form requires re-entering shipping and billing information without providing a 'Same as shipping' checkbox or auto-fill option (WCAG 2.2 SC 3.3.7 Redundant Entry).",
                "Add a checkbox (e.g. 'Billing address is the same as shipping address') or populate with previously entered user data to reduce cognitive burden."
            );
        }

        return null;
    }
}
