<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMDocument;
use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\AbstractRule as DocumentAbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class NavAccessibleName extends DocumentAbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_4_1_NAV_LABEL';
    }

    public function getDescription(): string
    {
        return 'When multiple navigation landmarks (<nav> or role="navigation") exist on a page, each must have a unique aria-label or aria-labelledby.';
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

    public function check(DOMDocument $doc): array
    {
        $xpath = new DOMXPath($doc);
        $navs = $xpath->query('//nav | //*[@role="navigation"]');
        if ($navs === false || $navs->length <= 1) {
            return [];
        }

        $violations = [];
        $seenLabels = [];

        foreach ($navs as $nav) {
            if (!$nav instanceof DOMElement) {
                continue;
            }

            $label = trim($nav->getAttribute('aria-label'));
            $labelledBy = trim($nav->getAttribute('aria-labelledby'));

            if ($label === '' && $labelledBy === '') {
                $violations[] = $this->createViolation(
                    'Page contains multiple navigation landmarks, but this <nav> is missing an aria-label or aria-labelledby to distinguish it.',
                    $nav,
                    'Add a unique aria-label (e.g., aria-label="Main Navigation", aria-label="Footer Links", aria-label="Pagination").'
                );
                continue;
            }

            $key = strtolower($label !== '' ? $label : 'id:' . $labelledBy);
            if (isset($seenLabels[$key])) {
                $violations[] = $this->createViolation(
                    sprintf('Multiple navigation landmarks share the duplicate accessible name "%s".', $label !== '' ? $label : $labelledBy),
                    $nav,
                    'Provide a distinct aria-label for each navigation landmark on the page.'
                );
            } else {
                $seenLabels[$key] = true;
            }
        }

        return $violations;
    }
}
