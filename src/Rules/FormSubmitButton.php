<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class FormSubmitButton extends AbstractRule
{
    public function getId(): string { return 'WCAG_3_2_2_FORM_SUBMIT'; }
    public function getDescription(): string { return 'Every form must contain a submit button to enable keyboard submission.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'form') {
            return null;
        }

        $hasSubmit = false;
        
        $buttons = $element->getElementsByTagName('button');
        foreach ($buttons as $btn) {
            $type = strtolower($btn->getAttribute('type') ?: 'submit');
            if ($type === 'submit') {
                $hasSubmit = true;
                break;
            }
        }

        if (!$hasSubmit) {
            $inputs = $element->getElementsByTagName('input');
            foreach ($inputs as $input) {
                $type = strtolower($input->getAttribute('type'));
                if ($type === 'submit' || $type === 'image') {
                    $hasSubmit = true;
                    break;
                }
            }
        }

        if (!$hasSubmit) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add a submit control, such as a <button type="submit"> or <input type="submit"> inside the form.'
            );
        }

        return null;
    }
}
