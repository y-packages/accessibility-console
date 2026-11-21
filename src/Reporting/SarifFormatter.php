<?php

namespace YakNet\AccessibilityConsole\Reporting;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;

class SarifFormatter implements FormatterInterface
{
    /**
     * @param Violation[] $violations
     * @param int $baselinedCount
     * @param SymfonyStyle $io
     * @return int
     */
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $sarifJson = $this->render($violations, $baselinedCount);
        $io->write($sarifJson);

        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Render the violations in SARIF 2.1.0 JSON format.
     *
     * @param Violation[] $violations
     * @param int $baselinedCount
     * @return string
     */
    public function render(array $violations, int $baselinedCount = 0): string
    {
        $rulesMap = [];
        $results = [];

        foreach ($violations as $v) {
            $ruleId = $v->ruleId;

            // Register rule metadata if not already present
            if (!isset($rulesMap[$ruleId])) {
                $rulesMap[$ruleId] = [
                    'id' => $ruleId,
                    'name' => $this->sanitizeRuleName($ruleId),
                    'shortDescription' => [
                        'text' => $v->message,
                    ],
                    'fullDescription' => [
                        'text' => $v->message,
                    ],
                    'defaultConfiguration' => [
                        'level' => $this->mapSeverityToSarifLevel($v->severity),
                    ],
                    'properties' => [
                        'tags' => [
                            'accessibility',
                            'a11y',
                            'wcag',
                            'wcag-' . strtolower($v->standard->value),
                        ],
                    ],
                ];
            }

            // Location details
            $file = $v->location['file'] ?? 'unknown';
            $line = isset($v->location['line']) ? max(1, (int)$v->location['line']) : 1;
            $column = isset($v->location['column']) ? max(1, (int)$v->location['column']) : 1;

            /** @var array<string, mixed> $region */
            $region = [
                'startLine' => $line,
                'startColumn' => $column,
            ];

            if ($v->htmlSnippet !== '') {
                $region['snippet'] = [
                    'text' => $v->htmlSnippet,
                ];
            }

            /** @var array<string, mixed> $locationEntry */
            $locationEntry = [
                'physicalLocation' => [
                    'artifactLocation' => [
                        'uri' => str_replace('\\', '/', $file),
                    ],
                    'region' => $region,
                ],
            ];

            /** @var array<string, mixed> $resultItem */
            $resultItem = [
                'ruleId' => $ruleId,
                'level' => $this->mapSeverityToSarifLevel($v->severity),
                'message' => [
                    'text' => $v->message,
                ],
                'locations' => [
                    $locationEntry,
                ],
            ];

            if ($v->fixSuggestion !== null && $v->fixSuggestion !== '') {
                $resultItem['fixes'] = [
                    [
                        'description' => [
                            'text' => 'Suggested accessibility fix',
                        ],
                    ],
                ];
            }

            $results[] = $resultItem;
        }

        $sarif = [
            '$schema' => 'https://json.schemastore.org/sarif-2.1.0.json',
            'version' => '2.1.0',
            'runs' => [
                [
                    'tool' => [
                        'driver' => [
                            'name' => 'YakNet Accessibility Console',
                            'informationUri' => 'https://forum.yak.net.tr',
                            'semanticVersion' => '4.0.0',
                            'rules' => array_values($rulesMap),
                        ],
                    ],
                    'results' => $results,
                ],
            ],
        ];

        return (string)json_encode($sarif, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function mapSeverityToSarifLevel(Severity $severity): string
    {
        return match ($severity) {
            Severity::CRITICAL, Severity::ERROR => 'error',
            Severity::WARNING => 'warning',
            Severity::INFO => 'note',
        };
    }

    private function sanitizeRuleName(string $ruleId): string
    {
        $cleaned = preg_replace('/^WCAG_\d+_\d+_\d+_?/', '', $ruleId);
        $cleaned = is_string($cleaned) ? $cleaned : $ruleId;
        $words = explode('_', strtolower($cleaned));
        return implode('', array_map('ucfirst', $words));
    }
}
