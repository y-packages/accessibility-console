<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaModalBackdrop extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_ARIA_MODAL';
    }

    public function getDescription(): string
    {
        return 'Custom modal dialog containers (role="dialog" or role="alertdialog") must declare aria-modal="true" to isolate screen reader navigation.';
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
        $tag = strtolower($element->tagName);
        if ($tag === 'dialog') {
            return null; // Native <dialog> handles modal state via showModal()
        }

        $role = strtolower(trim($element->getAttribute('role')));
        if (!in_array($role, ['dialog', 'alertdialog'], true)) {
            return null;
        }

        if (!$element->hasAttribute('aria-modal')) {
            return $this->createViolation(
                $element,
                sprintf('Custom element with role="%s" is missing aria-modal="true" (or "false" for non-modal dialogs).', $role),
                'Add aria-modal="true" on modal dialogs so assistive technologies constrain virtual cursor navigation inside the dialog.'
            );
        }

        return null;
    }
}
