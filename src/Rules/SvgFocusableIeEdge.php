<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class SvgFocusableIeEdge extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_1_1_SVG_FOCUSABLE';
    }

    public function getDescription(): string
    {
        return 'Decorative SVGs (aria-hidden="true") must include focusable="false" and must not contain focusable links or controls.';
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
        if (strtolower($element->tagName) !== 'svg') {
            return null;
        }

        if (strtolower(trim($element->getAttribute('aria-hidden'))) !== 'true') {
            return null;
        }

        $doc = $element->ownerDocument;
        if ($doc !== null) {
            $xpath = new DOMXPath($doc);
            $focusableChildren = $xpath->query('.//a[@href] | .//*[@tabindex and @tabindex != "-1"]', $element);
            if ($focusableChildren !== false && $focusableChildren->length > 0) {
                return $this->createViolation(
                    $element,
                    'Decorative <svg aria-hidden="true"> contains focusable child elements, creating invisible keyboard tab stops.',
                    'Remove interactive links/tabindex from decorative SVGs or remove aria-hidden="true" and provide an accessible name.'
                );
            }
        }

        if (strtolower(trim($element->getAttribute('focusable'))) !== 'false') {
            return $this->createViolation(
                $element,
                'Decorative <svg aria-hidden="true"> is missing focusable="false", which may allow stray keyboard focus in legacy browsers and assistive tech.',
                'Add focusable="false" alongside aria-hidden="true" on decorative <svg> elements.'
            );
        }

        return null;
    }
}
