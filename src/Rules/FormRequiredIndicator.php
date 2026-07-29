<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FormRequiredIndicator extends AbstractRule
{
    public function getId(): string { return 'WCAG_3_3_2_FORM_REQUIRED_INDICATOR'; }
    public function getDescription(): string { return 'Required form fields should have aria-required="true".'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if (!in_array($tag, ['input', 'select', 'textarea'])) {
            return null;
        }

        if ($element->hasAttribute('required')) {
            $ariaRequired = strtolower(trim($element->getAttribute('aria-required')));
            if ($ariaRequired !== 'true') {
                // If it has 'required' but not aria-required="true", warn
                return $this->createViolation(
                    $element,
                    "Zorunlu form alanı aria-required=\"true\" niteliğini içermiyor.",
                    "Ekran okuyucuların form alanının zorunlu olduğunu doğru şekilde anons edebilmesi için aria-required=\"true\" niteliğini eklemeyi düşünün."
                );
            }
        }

        return null;
    }
}
