<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class RoleImgAlt extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_1_1_ROLE_IMG';
    }

    public function getDescription(): string
    {
        return 'Elements with role="img" must have an accessible text alternative.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 2;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) === 'img') {
            return null; // Handled by ImgAltText
        }

        $roles = explode(' ', strtolower(trim($element->getAttribute('role'))));
        if (!in_array('img', $roles, true)) {
            return null;
        }

        if ($element->getAttribute('aria-hidden') === 'true') {
            return null;
        }

        $ariaLabel = trim($element->getAttribute('aria-label'));
        $ariaLabelledby = trim($element->getAttribute('aria-labelledby'));
        $title = trim($element->getAttribute('title'));
        $text = trim($element->textContent);

        if ($ariaLabel !== '' || $ariaLabelledby !== '' || $title !== '' || $text !== '') {
            return null;
        }

        return $this->createViolation(
            $element,
            "Element with role=\"img\" (<{$element->tagName}>) does not have an accessible name.",
            "Provide an accessible description using aria-label, aria-labelledby, or title, or hide it if decorative using aria-hidden=\"true\"."
        );
    }
}
