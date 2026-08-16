<?php

namespace YakNet\AccessibilityConsole\Syntax;

class SyntaxIssue
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly int $line,
        public readonly int $column = 1,
        public readonly string $snippet = '',
        public readonly ?string $fixSuggestion = null,
        public readonly string $severity = 'error'
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'line' => $this->line,
            'column' => $this->column,
            'snippet' => $this->snippet,
            'fixSuggestion' => $this->fixSuggestion,
            'severity' => $this->severity,
        ];
    }
}
