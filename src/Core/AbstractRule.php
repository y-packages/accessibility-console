<?php

namespace YakNet\AccessibilityConsole\Core;

abstract class AbstractRule
{
    /**
     * @return Violation[]
     */
    abstract public function check(\DOMDocument $doc): array;

    /**
     * Get the WCAG standard ID (e.g., WCAG 2.1 1.1.1)
     */
    abstract public function getStandardId(): string;

    /**
     * Get the severity level (Critical, Major, Minor)
     */
    abstract public function getSeverity(): string;

    protected function createViolation(string $message, \DOMElement $element): Violation
    {
        return new Violation(
            message: $message,
            htmlSnippet: $element->ownerDocument->saveHTML($element),
            severity: $this->getSeverity(),
            standard: $this->getStandardId()
        );
    }
}
