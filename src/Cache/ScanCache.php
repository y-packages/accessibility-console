<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Cache;

use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

/**
 * Incremental content-hash cache for static accessibility scans.
 * Stores violation results keyed by file path, content SHA-256 hash, and active rule-set signature
 * to avoid re-parsing unchanged templates on repeated runs.
 */
class ScanCache
{
    /**
     * @var array<string, array{hash: string, violations: array<int, array<string, mixed>>}>
     */
    private array $entries = [];

    private string $rulesSignature = '';
    private int $hits = 0;
    private int $misses = 0;

    public function __construct(
        private string $cacheFile = '.a11y-cache.json',
        string $rulesSignature = ''
    ) {
        $this->rulesSignature = $rulesSignature;
    }

    public function setRulesSignature(string $signature): void
    {
        if ($this->rulesSignature !== '' && $this->rulesSignature !== $signature) {
            $this->entries = [];
        }
        $this->rulesSignature = $signature;
    }

    public function load(): void
    {
        if (!file_exists($this->cacheFile)) {
            return;
        }

        $raw = @file_get_contents($this->cacheFile);
        if ($raw === false || trim($raw) === '') {
            return;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return;
        }

        $storedSig = is_scalar($decoded['rules_signature'] ?? null) ? (string)$decoded['rules_signature'] : '';
        if ($this->rulesSignature !== '' && $storedSig !== $this->rulesSignature) {
            $this->entries = [];
            return;
        }

        if (isset($decoded['entries']) && is_array($decoded['entries'])) {
            foreach ($decoded['entries'] as $path => $entry) {
                if (!is_string($path) || !is_array($entry)) {
                    continue;
                }
                if (!is_string($entry['hash'] ?? null) || !is_array($entry['violations'] ?? null)) {
                    continue;
                }
                /** @var array<int, array<string, mixed>> $violationsList */
                $violationsList = array_values(array_filter($entry['violations'], 'is_array'));
                $this->entries[$path] = [
                    'hash' => $entry['hash'],
                    'violations' => $violationsList,
                ];
            }
        }
    }

    /**
     * Retrieve cached violations for a file if its content hash matches; otherwise return null.
     *
     * @return Violation[]|null
     */
    public function get(string $relativePath, string $content): ?array
    {
        $hash = hash('sha256', $content);
        if (!isset($this->entries[$relativePath]) || $this->entries[$relativePath]['hash'] !== $hash) {
            $this->misses++;
            return null;
        }

        $this->hits++;
        $violations = [];
        foreach ($this->entries[$relativePath]['violations'] as $row) {
            $ruleId = is_scalar($row['ruleId'] ?? null) ? (string)$row['ruleId'] : '';
            $message = is_scalar($row['message'] ?? null) ? (string)$row['message'] : '';
            $sevRaw = is_scalar($row['severity'] ?? null) ? (string)$row['severity'] : 'error';
            $stdRaw = is_scalar($row['standard'] ?? null) ? (string)$row['standard'] : 'A';
            $snippet = is_scalar($row['htmlSnippet'] ?? null) ? (string)$row['htmlSnippet'] : '';
            $fix = is_string($row['fixSuggestion'] ?? null) ? $row['fixSuggestion'] : null;

            $location = null;
            if (isset($row['location']) && is_array($row['location'])) {
                $locLine = is_numeric($row['location']['line'] ?? null) ? (int)$row['location']['line'] : 1;
                $location = ['line' => $locLine];
                if (is_string($row['location']['file'] ?? null)) {
                    $location['file'] = $row['location']['file'];
                }
                if (is_numeric($row['location']['column'] ?? null)) {
                    $location['column'] = (int)$row['location']['column'];
                }
            }

            $violations[] = new Violation(
                ruleId: $ruleId,
                message: $message,
                severity: Severity::tryFrom($sevRaw) ?? Severity::ERROR,
                standard: WCAGStandard::tryFrom($stdRaw) ?? WCAGStandard::A,
                htmlSnippet: $snippet,
                location: $location,
                fixSuggestion: $fix
            );
        }

        return $violations;
    }

    /**
     * Store violations for a scanned file.
     *
     * @param Violation[] $violations
     */
    public function put(string $relativePath, string $content, array $violations): void
    {
        $this->entries[$relativePath] = [
            'hash' => hash('sha256', $content),
            'violations' => array_map(static fn(Violation $v): array => $v->toArray(), $violations),
        ];
    }

    public function save(): bool
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir) && $dir !== '.') {
            @mkdir($dir, 0775, true);
        }

        $payload = [
            'version' => '4.0.0',
            'updated_at' => date('Y-m-d\TH:i:sP'),
            'rules_signature' => $this->rulesSignature,
            'entries' => $this->entries,
        ];

        return file_put_contents(
            $this->cacheFile,
            (string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ) !== false;
    }

    public function clear(): bool
    {
        $this->entries = [];
        $this->hits = 0;
        $this->misses = 0;
        if (file_exists($this->cacheFile)) {
            return @unlink($this->cacheFile);
        }
        return true;
    }

    /**
     * @return array{hits: int, misses: int, entries: int, hit_rate: float}
     */
    public function getStats(): array
    {
        $total = $this->hits + $this->misses;
        $rate = $total > 0 ? round(($this->hits / $total) * 100, 1) : 0.0;
        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'entries' => count($this->entries),
            'hit_rate' => $rate,
        ];
    }
}
