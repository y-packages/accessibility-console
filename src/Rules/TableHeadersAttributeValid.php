<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableHeadersAttributeValid extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_TABLE_HEADERS_ATTR';
    }

    public function getDescription(): string
    {
        return 'The headers attribute on table cells must refer to valid <th> elements within the same table.';
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
        return 3;
    }

    public function check(DOMElement $element): ?Violation
    {
        $tag = strtolower($element->tagName);
        if ($tag !== 'td' && $tag !== 'th') {
            return null;
        }

        if (!$element->hasAttribute('headers')) {
            return null;
        }

        $headersStr = trim($element->getAttribute('headers'));
        if ($headersStr === '') {
            return $this->createViolation(
                $element,
                "Empty 'headers' attribute found on <{$tag}>.",
                "Provide space-separated IDs of the corresponding <th> header cells or remove the empty attribute."
            );
        }

        // Find enclosing table
        $table = $element->parentNode;
        while ($table !== null && (!($table instanceof DOMElement) || strtolower($table->tagName) !== 'table')) {
            $table = $table->parentNode;
        }

        if (!$table instanceof DOMElement) {
            return null;
        }

        $headerIds = array_filter(explode(' ', $headersStr));
        $invalidIds = [];

        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }

        $xpath = new \DOMXPath($doc);

        foreach ($headerIds as $hid) {
            $matchingTh = $xpath->query(".//th[@id='{$hid}']", $table);
            if ($matchingTh === false || $matchingTh->length === 0) {
                $invalidIds[] = $hid;
            }
        }

        if (!empty($invalidIds)) {
            $invalidStr = implode(', ', $invalidIds);
            return $this->createViolation(
                $element,
                "The headers attribute refers to ID(s) not found as <th> elements within the table: {$invalidStr}.",
                "Ensure each ID specified in the 'headers' attribute matches the 'id' of a <th> element in this table."
            );
        }

        return null;
    }
}
