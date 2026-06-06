<?php

namespace YakNet\AccessibilityConsole\Core;

abstract class AbstractRule
{
    /**
     * @return Violation[]
     */
    abstract public function check(\DOMDocument $doc): array;

    /**
     * Get the WCAG standard Enum
     */
    abstract public function getStandard(): WCAGStandard;

    /**
     * Get the severity level Enum
     */
    abstract public function getSeverity(): Severity;

    protected function createViolation(string $message, \DOMElement $element): Violation
    {
        $doc = $element->ownerDocument;
        $html = '';
        if ($doc !== null) {
            $htmlVal = $doc->saveHTML($element);
            if (is_string($htmlVal)) {
                $html = $htmlVal;
            }
        }

        return new Violation(
            ruleId: (new \ReflectionClass($this))->getShortName(),
            message: $message,
            severity: $this->getSeverity(),
            standard: $this->getStandard(),
            htmlSnippet: $html
        );
    }
}
