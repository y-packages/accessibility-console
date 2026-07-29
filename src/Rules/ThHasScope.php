<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ThHasScope extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_TH_SCOPE'; }
    public function getDescription(): string { return '<th> elements in complex tables should have a scope attribute.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'th') {
            return null;
        }

        if ($element->hasAttribute('scope')) {
            return null;
        }

        // Find parent table
        $table = $element->parentNode;
        while ($table !== null && !($table instanceof DOMElement && strtolower($table->tagName) === 'table')) {
            $table = $table->parentNode;
        }

        if (!$table instanceof DOMElement) {
            return null;
        }

        // Determine if table is complex
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $thRows = $xpath->query('.//tr[th]', $table);
        $hasColspan = $xpath->query('.//*[@colspan or @rowspan]', $table);

        if ($thRows !== false && $hasColspan !== false && ($thRows->length > 1 || $hasColspan->length > 0)) {
            return $this->createViolation(
                $element,
                'Karmaşık tablolardaki <th> etiketleri scope niteliği (col, row vb.) içermelidir. / <th> elements in complex tables must have a scope attribute.',
                '<th> etiketine uygun "scope" niteliğini (col, row, colgroup, rowgroup) ekleyin. / Add appropriate "scope" attribute to <th>.'
            );
        }

        return null;
    }
}
