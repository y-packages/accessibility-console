<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LinkTextGeneric extends AbstractRule
{
    public function getId(): string { return 'WCAG_2_4_4_LINK_GENERIC'; }
    public function getDescription(): string { return 'Links must have descriptive text. Generic text like "click here" or "read more" is not accessible.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }

    /** @var array<int, string> */
    private array $genericWords = [
        'click here', 'click', 'read more', 'more', 'here', 'download', 'go', 'details', 'link', 'website', 'page', 'info',
        'tıkla', 'tıklayın', 'devamı', 'devamını oku', 'indir', 'git', 'detay', 'detaylar', 'link', 'site', 'sayfa', 'bilgi'
    ];

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a') {
            return null;
        }

        $text = trim($element->textContent);
        $cleanText = strtolower(preg_replace('/\s+/', ' ', $text) ?? '');

        if (in_array($cleanText, $this->genericWords, true)) {
            // Check for descriptive attributes
            if ($element->hasAttribute('aria-label') || $element->hasAttribute('aria-labelledby') || $element->hasAttribute('title')) {
                return null;
            }

            return $this->createViolation(
                $element,
                $this->getDescription(),
                'Change the link text to describe its target, or add an aria-label attribute to provide context.'
            );
        }

        return null;
    }
}
