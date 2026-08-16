<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class MetaCharsetPresent extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_1_1_META_CHARSET';
    }

    public function getDescription(): string
    {
        return 'HTML documents should specify character encoding (meta charset) inside the <head> element.';
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
        return 2;
    }

    public function check(\DOMDocument $doc): array
    {
        $heads = $doc->getElementsByTagName('head');
        if ($heads->length === 0) {
            return [];
        }

        $head = $heads->item(0);
        if (!$head instanceof DOMElement) {
            return [];
        }

        $hasCharset = false;
        $metas = $head->getElementsByTagName('meta');

        foreach ($metas as $meta) {
            if ($meta->hasAttribute('charset') && trim($meta->getAttribute('charset')) !== '') {
                $hasCharset = true;
                break;
            }

            if (strtolower($meta->getAttribute('http-equiv')) === 'content-type') {
                $content = strtolower($meta->getAttribute('content'));
                if (str_contains($content, 'charset=')) {
                    $hasCharset = true;
                    break;
                }
            }
        }

        if (!$hasCharset) {
            return [
                $this->createViolation(
                    "The document <head> is missing a character encoding declaration (<meta charset=\"utf-8\">).",
                    $head,
                    "Add <meta charset=\"utf-8\"> as the first child of the <head> tag to prevent screen reader character misinterpretations."
                )
            ];
        }

        return [];
    }
}
