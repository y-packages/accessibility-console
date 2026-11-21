<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class CharacterKeyShortcuts extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_1_4_CHAR_KEY_SHORTCUTS';
    }

    public function getDescription(): string
    {
        return 'Keyboard shortcuts defined via aria-keyshortcuts or data-hotkey must use a modifier key (Control, Alt, Meta) to avoid speech-input conflicts (WCAG 2.1.4).';
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
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        $attrs = ['aria-keyshortcuts', 'data-hotkey', 'data-shortcut'];

        foreach ($attrs as $attr) {
            if (!$element->hasAttribute($attr)) {
                continue;
            }
            $value = trim($element->getAttribute($attr));
            if ($value === '') {
                continue;
            }

            // Multiple shortcuts can be space-separated
            $shortcuts = preg_split('/\s+/', $value);
            if (!is_array($shortcuts)) {
                continue;
            }

            foreach ($shortcuts as $shortcut) {
                // Single printable character without modifier (e.g. "k", "/", "?", "j", "Shift+a" is also printable)
                $clean = trim($shortcut);
                if ($clean === '') {
                    continue;
                }

                $hasNonPrintableModifier = (bool)preg_match('/^(?:Control|Ctrl|Alt|Meta|Command|Cmd|AltGraph)\+/i', $clean);
                $isSingleChar = mb_strlen($clean) === 1 && preg_match('/^[a-zA-Z0-9\p{P}\p{S}]$/u', $clean);
                $isShiftOnlyChar = (bool)preg_match('/^Shift\+[^\s+]{1}$/i', $clean);

                if (!$hasNonPrintableModifier && ($isSingleChar || $isShiftOnlyChar)) {
                    return $this->createViolation(
                        $element,
                        sprintf('Attribute %s="%s" defines a single-character keyboard shortcut without a non-printable modifier (Control/Alt/Meta), violating WCAG 2.1.4.', $attr, $clean),
                        sprintf('Require a modifier key (e.g., %s="Alt+%s") or provide a mechanism to remap or disable the shortcut.', $attr, strtoupper($clean))
                    );
                }
            }
        }

        return null;
    }
}
