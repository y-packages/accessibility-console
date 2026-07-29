<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

/**
 * WCAG 3.1.2 — Checks that when both `lang` and `xml:lang` are present on <html>,
 * their primary language subtags match.
 *
 * A mismatch between these attributes can cause assistive technologies to
 * announce content in the wrong language.
 */
class LangMismatch extends AbstractRule
{
    public function getId(): string { return 'WCAG_3_1_2_LANG_MISMATCH'; }
    public function getDescription(): string { return 'The lang and xml:lang attributes on <html> must have matching primary language subtags.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::ERROR; }
    public function getLevel(): int { return 3; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'html') {
            return null;
        }

        $lang = $element->getAttribute('lang');
        $xmlLang = $element->getAttribute('xml:lang');

        // Only check when both are present
        if (empty($lang) || empty($xmlLang)) {
            return null;
        }

        $primaryLang = $this->getPrimarySubtag($lang);
        $primaryXmlLang = $this->getPrimarySubtag($xmlLang);

        if (strtolower($primaryLang) !== strtolower($primaryXmlLang)) {
            return $this->createViolation(
                $element,
                "lang=\"{$lang}\" ile xml:lang=\"{$xmlLang}\" arasında birincil dil uyumsuzluğu var.",
                'lang ve xml:lang niteliklerinin birincil dil alt etiketlerinin eşleştiğinden emin olun (örn. her ikisi de "en" veya "tr").'
            );
        }

        return null;
    }

    /**
     * Extracts the primary language subtag from a BCP 47 language tag.
     * e.g. "en-US" → "en", "tr" → "tr"
     */
    private function getPrimarySubtag(string $langTag): string
    {
        $parts = explode('-', trim($langTag));
        return $parts[0];
    }
}
