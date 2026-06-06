<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class PageTitle extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_2_PAGE_TITLE'; }
    public function getDescription(): string { return 'Web pages must have a non-empty title element in the head.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::ERROR; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'head') {
            return null;
        }

        $titles = $element->getElementsByTagName('title');
        
        if ($titles->length === 0) {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Add a <title> element inside the <head>.'
            );
        }

        $titleText = '';
        $firstTitle = $titles->item(0);
        if ($firstTitle !== null) {
            $titleText = trim($firstTitle->textContent);
        }

        if ($titleText === '') {
            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Provide descriptive text inside the <title> element.'
            );
        }

        return null;
    }
}
