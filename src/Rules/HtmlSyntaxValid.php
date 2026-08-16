<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;
use YakNet\AccessibilityConsole\Syntax\HtmlSyntaxLinter;

class HtmlSyntaxValid extends AbstractRule
{
    private HtmlSyntaxLinter $linter;

    public function __construct(?HtmlSyntaxLinter $linter = null)
    {
        $this->linter = $linter ?? new HtmlSyntaxLinter();
    }

    public function getId(): string
    {
        return 'WCAG_4_1_1_HTML_PARSING_SYNTAX';
    }

    public function getDescription(): string
    {
        return 'HTML documents must be well-formed without duplicate attributes, mismatched tags, or malformed syntax (WCAG 4.1.1).';
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
        return 2;
    }

    public function check(\DOMDocument $doc): array
    {
        $rawHtml = property_exists($doc, 'rawHtml') && is_string($doc->rawHtml)
            ? $doc->rawHtml
            : $doc->saveHTML();

        if (empty($rawHtml) || trim($rawHtml) === '') {
            return [];
        }

        $issues = $this->linter->lint($rawHtml);
        $violations = [];

        foreach ($issues as $issue) {
            $severity = ($issue->severity === 'warning') ? Severity::WARNING : Severity::ERROR;

            $violations[] = new Violation(
                ruleId: $this->getId(),
                message: "[{$issue->code}] {$issue->message}",
                severity: $severity,
                standard: $this->getStandard(),
                htmlSnippet: $issue->snippet,
                location: [
                    'line' => $issue->line,
                    'column' => $issue->column,
                ],
                fixSuggestion: $issue->fixSuggestion
            );
        }

        return $violations;
    }
}
