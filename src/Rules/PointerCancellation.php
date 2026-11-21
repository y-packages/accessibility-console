<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class PointerCancellation extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_5_2_POINTER_CANCELLATION';
    }

    public function getDescription(): string
    {
        return 'Interactive controls should trigger actions on pointer up-events (onclick/onmouseup) rather than down-events (onmousedown/ontouchstart/onpointerdown) (WCAG 2.5.2).';
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
        $downEvents = ['onmousedown', 'ontouchstart', 'onpointerdown'];
        $hasDownEvent = null;

        foreach ($downEvents as $evt) {
            if ($element->hasAttribute($evt) && trim($element->getAttribute($evt)) !== '') {
                $hasDownEvent = $evt;
                break;
            }
        }

        if ($hasDownEvent === null) {
            return null;
        }

        // If element also has up/click event or is explicitly draggable/slider/canvas, down-event may be legitimate
        $tag = strtolower($element->tagName);
        $role = strtolower($element->getAttribute('role'));
        if ($tag === 'canvas' || $role === 'slider' || $element->getAttribute('draggable') === 'true') {
            return null;
        }

        $hasUpEvent = $element->hasAttribute('onclick')
            || $element->hasAttribute('onmouseup')
            || $element->hasAttribute('ontouchend')
            || $element->hasAttribute('onpointerup');

        if (!$hasUpEvent) {
            return $this->createViolation(
                $element,
                sprintf('Element executes an action on "%s" down-event without an up-event or click handler, preventing pointer cancellation (WCAG 2.5.2).', $hasDownEvent),
                'Bind interactive actions to onclick / onmouseup / onpointerup so users can abort accidental pointer activation.'
            );
        }

        return null;
    }
}
