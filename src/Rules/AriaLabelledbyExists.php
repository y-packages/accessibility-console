<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaLabelledbyExists extends AbstractRule
{
    public function getId(): string { return 'WCAG_4_1_2_ARIA_LABELLEDBY'; }
    public function getDescription(): string { return 'aria-labelledby and aria-describedby attributes must refer to existing element IDs.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        $attrs = ['aria-labelledby', 'aria-describedby'];
        foreach ($attrs as $attr) {
            if ($element->hasAttribute($attr)) {
                $ids = preg_split('/\s+/', trim($element->getAttribute($attr)));
                if ($ids === false) {
                    continue;
                }

                $doc = $element->ownerDocument;
                if (!$doc) {
                    continue;
                }

                foreach ($ids as $id) {
                    if ($id === '') {
                        continue;
                    }

                    $target = $doc->getElementById($id);
                    if (!$target) {
                        // XPath fallback in case DOMDocument getElementById fails (which is common without DTD/XML schemas)
                        $xpath = new \DOMXPath($doc);
                        $query = $xpath->query("//*[@id='" . addslashes($id) . "']");
                        if ($query === false || $query->length === 0) {
                            return $this->createViolation(
                                $element,
                                "The attribute $attr refers to a non-existent ID: \"$id\".",
                                "Ensure the element referenced by ID \"$id\" exists in the document, or correct the ID spelling."
                            );
                        }
                    }
                }
            }
        }

        return null;
    }
}
