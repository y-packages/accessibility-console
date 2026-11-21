<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Policy;

class PolicyResult
{
    /**
     * @param bool $passed
     * @param string[] $failures
     * @param array<string, mixed> $summary
     */
    public function __construct(
        public readonly bool $passed,
        public readonly array $failures = [],
        public readonly array $summary = []
    ) {}

    /**
     * @return array{passed: bool, failures: string[], summary: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'failures' => $this->failures,
            'summary' => $this->summary,
        ];
    }
}
