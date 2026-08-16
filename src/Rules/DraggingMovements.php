<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class DraggingMovements extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_5_7_DRAGGING_MOVEMENTS';
    }

    public function getDescription(): string
    {
        return 'Elements supporting drag-and-drop operations must provide a single-pointer alternative (WCAG 2.2 SC 2.5.7).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
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
        $isDraggable = strtolower(trim($element->getAttribute('draggable'))) === 'true';
        $hasDragEvent = $element->hasAttribute('ondragstart') || $element->hasAttribute('ondrop');

        if (!$isDraggable && !$hasDragEvent) {
            return null;
        }

        // Check if element or child has alternative buttons/actions or aria-grabbed/keyshortcuts
        $hasKeyboardShortcut = $element->hasAttribute('aria-keyshortcuts');
        $hasGrabbed = $element->hasAttribute('aria-grabbed');
        $hasRole = $element->hasAttribute('role');

        $doc = $element->ownerDocument;
        $hasButtonsInside = false;
        if ($doc !== null) {
            $buttons = $element->getElementsByTagName('button');
            if ($buttons->length > 0) {
                $hasButtonsInside = true;
            }
        }

        if (!$hasKeyboardShortcut && !$hasGrabbed && !$hasButtonsInside) {
            return $this->createViolation(
                $element,
                "Draggable element lacks a single-pointer or keyboard-accessible alternative (WCAG 2.2 SC 2.5.7).",
                "Provide alternative click buttons (e.g. Move Up / Move Down) or keyboard commands for users unable to perform dragging movements."
            );
        }

        return null;
    }
}
