<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMDocument;
use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\AbstractRule as DocumentAbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ConsistentHelp extends DocumentAbstractRule
{
    public function getId(): string
    {
        return 'WCAG_3_2_6_CONSISTENT_HELP';
    }

    public function getDescription(): string
    {
        return 'Interactive full-page documents with forms should provide an accessible help, contact, or support mechanism (WCAG 2.2 SC 3.2.6).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::INFO;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMDocument $doc): array
    {
        $xpath = new DOMXPath($doc);

        // Only evaluate full pages that include a <body>, a <header> or <footer>, and a <form>
        $bodies = $xpath->query('//body');
        $forms = $xpath->query('//form');
        $landmarks = $xpath->query('//header | //footer | //nav');

        if ($bodies === false || $bodies->length === 0
            || $forms === false || $forms->length === 0
            || $landmarks === false || $landmarks->length === 0) {
            return [];
        }

        $helpKeywords = [
            'help', 'support', 'contact', 'faq', 'assist', 'chat', 'customer service',
            'yardım', 'destek', 'iletişim', 'sss', 'canlı destek', 'müşteri hizmetleri',
        ];

        $linksAndButtons = $xpath->query('//a | //button');
        if ($linksAndButtons !== false) {
            foreach ($linksAndButtons as $el) {
                if (!$el instanceof DOMElement) {
                    continue;
                }
                $text = mb_strtolower(trim($el->textContent . ' ' . $el->getAttribute('aria-label') . ' ' . $el->getAttribute('title') . ' ' . $el->getAttribute('href')));
                if (str_starts_with($el->getAttribute('href'), 'mailto:') || str_starts_with($el->getAttribute('href'), 'tel:')) {
                    return [];
                }
                foreach ($helpKeywords as $kw) {
                    if (str_contains($text, $kw)) {
                        return [];
                    }
                }
            }
        }

        $body = $bodies->item(0);
        if ($body instanceof DOMElement) {
            return [
                $this->createViolation(
                    'Page contains interactive forms and navigation landmarks but no detectable help, support, or contact link (WCAG 2.2 SC 3.2.6 Consistent Help).',
                    $body,
                    'Provide a consistent help or contact mechanism (e.g., <a href="/help">Help</a> or <a href="/contact">Contact Support</a>) in the header or footer.'
                ),
            ];
        }

        return [];
    }
}
