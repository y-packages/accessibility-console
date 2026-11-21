<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class InputTypeValidation extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_3_2_INPUT_TYPE_SEMANTIC';
    }

    public function getDescription(): string
    {
        return 'Use semantic HTML5 input types (email, tel, url, search) instead of generic type="text" to assist mobile keyboards and assistive technologies.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::INFO;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'input') {
            return null;
        }

        $type = strtolower(trim($element->getAttribute('type')));
        if ($type !== '' && $type !== 'text') {
            return null;
        }

        $identifier = strtolower($element->getAttribute('name') . ' ' . $element->getAttribute('id'));
        if (trim($identifier) === '') {
            return null;
        }

        $mappings = [
            'email' => '/\b(e?mail|eposta|e_posta)\b/',
            'tel' => '/\b(phone|tel|telephone|mobile|gsm|telefon|cep)\b/',
            'url' => '/\b(website|homepage|url|web_site)\b/',
        ];

        foreach ($mappings as $expectedType => $pattern) {
            if (preg_match($pattern, $identifier)) {
                return $this->createViolation(
                    $element,
                    sprintf('Input appears to collect "%s" data but uses generic type="text" instead of type="%s".', $expectedType, $expectedType),
                    sprintf('Change type="text" to type="%s" to expose native validation and optimized virtual keyboards.', $expectedType)
                );
            }
        }

        return null;
    }
}
