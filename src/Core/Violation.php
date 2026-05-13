<?php

namespace YakNet\AccessibilityConsole\Core;

readonly class Violation
{
    public function __construct(
        public string $ruleId,
        public string $message,
        public Severity $severity,
        public WCAGStandard $standard,
        public string $htmlSnippet,
        public ?array $location = null, // ['file' => '...', 'line' => 0]
        public ?string $fixSuggestion = null
    ) {}

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
