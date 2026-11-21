<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AriaBreadcrumbPattern extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_4_8_BREADCRUMB';
    }

    public function getDescription(): string
    {
        return 'Breadcrumb navigation landmarks must provide an accessible label (aria-label="Breadcrumb") and mark the current page with aria-current="page" (WCAG 2.4.8).';
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
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if ($tag !== 'nav' && strtolower($element->getAttribute('role')) !== 'navigation') {
            return null;
        }

        $classAndId = strtolower($element->getAttribute('class') . ' ' . $element->getAttribute('id'));
        $ariaLabel = strtolower(trim($element->getAttribute('aria-label')));
        $isBreadcrumb = str_contains($classAndId, 'breadcrumb') || str_contains($ariaLabel, 'breadcrumb');

        if (!$isBreadcrumb) {
            return null;
        }

        if ($ariaLabel === '' && trim($element->getAttribute('aria-labelledby')) === '') {
            return $this->createViolation(
                $element,
                'Breadcrumb navigation landmark is missing an aria-label (e.g., aria-label="Breadcrumb").',
                'Add aria-label="Breadcrumb" to the <nav> element.'
            );
        }

        $doc = $element->ownerDocument;
        if ($doc !== null) {
            $xpath = new DOMXPath($doc);
            $items = $xpath->query('.//li | .//a', $element);
            if ($items !== false && $items->length >= 2) {
                $current = $xpath->query('.//*[@aria-current="page"]', $element);
                if ($current !== false && $current->length === 0) {
                    return $this->createViolation(
                        $element,
                        'Breadcrumb navigation does not mark the active page with aria-current="page" (WCAG 2.4.8 Location).',
                        'Add aria-current="page" to the last breadcrumb item representing the current page.'
                    );
                }
            }
        }

        return null;
    }
}
