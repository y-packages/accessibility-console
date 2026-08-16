<?php

namespace YakNet\AccessibilityConsole\Core;

class Violation
{
    /**
     * @param array{file?: string, line: int, column?: int}|null $location
     */
    public function __construct(
        public string $ruleId,
        public string $message,
        public Severity $severity,
        public WCAGStandard $standard,
        public string $htmlSnippet = '',
        public ?array $location = null, // ['file' => '...', 'line' => 0]
        public ?string $fixSuggestion = null
    ) {}

    /**
     * @return array{ruleId: string, message: string, severity: string, standard: string, htmlSnippet: string, location: array{file?: string, line: int, column?: int}|null, fixSuggestion: string|null}
     */
    public function toArray(): array
    {
        return [
            'ruleId' => $this->ruleId,
            'message' => $this->message,
            'severity' => $this->severity->value,
            'standard' => $this->standard->value,
            'htmlSnippet' => $this->htmlSnippet,
            'location' => $this->location,
            'fixSuggestion' => $this->fixSuggestion,
        ];
    }
}
