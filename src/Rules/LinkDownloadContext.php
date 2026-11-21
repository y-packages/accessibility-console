<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class LinkDownloadContext extends AbstractRule
{
    /** @var array<string, string> */
    private const DOCUMENT_EXTENSIONS = [
        'pdf' => 'PDF',
        'doc' => 'DOC',
        'docx' => 'DOCX',
        'xls' => 'XLS',
        'xlsx' => 'XLSX',
        'ppt' => 'PPT',
        'pptx' => 'PPTX',
        'zip' => 'ZIP',
        'rar' => 'RAR',
        'csv' => 'CSV',
    ];

    public function getId(): string
    {
        return 'WCAG_2_4_4_LINK_DOWNLOAD_CONTEXT';
    }

    public function getDescription(): string
    {
        return 'Links to downloadable non-HTML documents (.pdf, .docx, .xlsx, .zip) should indicate the file format in the link text or aria-label.';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::INFO;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'a' || !$element->hasAttribute('href')) {
            return null;
        }

        $href = trim($element->getAttribute('href'));
        $path = parse_url($href, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!isset(self::DOCUMENT_EXTENSIONS[$ext])) {
            return null;
        }

        $accessibleText = mb_strtolower(trim(
            $element->textContent . ' ' . $element->getAttribute('aria-label') . ' ' . $element->getAttribute('title')
        ));

        $labelFormat = self::DOCUMENT_EXTENSIONS[$ext];
        $baseFormat = strtolower($ext);
        $altFormats = match ($ext) {
            'docx' => ['docx', 'doc', 'word'],
            'xlsx' => ['xlsx', 'xls', 'excel'],
            'pptx' => ['pptx', 'ppt', 'powerpoint'],
            default => [$baseFormat],
        };

        foreach ($altFormats as $token) {
            if (str_contains($accessibleText, $token)) {
                return null;
            }
        }

        return $this->createViolation(
            $element,
            sprintf('Link downloads a %s file ("%s") without mentioning the file format in its accessible name.', $labelFormat, basename($path)),
            sprintf('Include the file format in the link text or aria-label (e.g., "Annual Report (%s)").', $labelFormat)
        );
    }
}
