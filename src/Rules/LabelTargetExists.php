<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LabelTargetExists extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_LABEL_TARGET'; }
    public function getDescription(): string { return 'Label elements with a \'for\' attribute must target an existing element ID.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 2; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'label') {
            return null;
        }

        if ($element->hasAttribute('for')) {
            $forId = trim($element->getAttribute('for'));
            if ($forId === '') {
                return $this->createViolation(
                    $element,
                    $this->getDescription(),
                    'Provide a valid element ID in the for attribute.'
                );
            }

            $doc = $element->ownerDocument;
            if ($doc !== null) {
                $xpath = new \DOMXPath($doc);
                $elements = $xpath->query('//*[@id="' . $forId . '"]');
                if ($elements === false || $elements->length === 0) {
                    return $this->createViolation(
                        $element,
                        $this->getDescription(),
                        "Ensure the 'for' attribute value (\"$forId\") matches the ID of a form input element."
                    );
                }
            }
        }

        return null;
    }
}
