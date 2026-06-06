<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

abstract class AbstractRule implements RuleInterface
{
    abstract public function getId(): string;
    abstract public function getDescription(): string;
    abstract public function getStandard(): WCAGStandard;
    abstract public function getSeverity(): Severity;

    protected function createViolation(DOMElement $element, string $message, ?string $suggestion = null): Violation
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
            ruleId: $this->getId(),
            message: $message,
            severity: $this->getSeverity(),
            standard: $this->getStandard(),
            htmlSnippet: $html,
            fixSuggestion: $suggestion
        );
    }
}
