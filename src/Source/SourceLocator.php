<?php

namespace YakNet\AccessibilityConsole\Source;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SourceLocator
{
    /** @var array<int, string> */
    private array $extensions = ['php', 'html', 'twig', 'blade.php'];

    public function __construct(
        private readonly string $searchPath
    ) {}

    /**
     * @return array{file: string, line: int}|null
     */
    public function locate(string $snippet): ?array
    {
        if (!is_dir($this->searchPath) || !is_readable($this->searchPath)) {
            return null;
        }

        $replaced = preg_replace('/\s+/', ' ', $snippet);
        $cleanSnippet = is_string($replaced) ? trim($replaced) : '';
        if ($cleanSnippet === '') {
            return null;
        }

        $searchSnippet = mb_strlen($cleanSnippet) > 150 ? mb_substr($cleanSnippet, 0, 150) : $cleanSnippet;

        try {
            $directory = new RecursiveDirectoryIterator($this->searchPath, \FilesystemIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        } catch (\UnexpectedValueException) {
            return null;
        }

        $bestFuzzyMatch = null;
        $highestScore = 0;

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }

            if (!$this->isValidExtension($file->getFilename())) {
                continue;
            }

            try {
                $result = $this->searchInFile($file->getPathname(), $searchSnippet, $cleanSnippet);
                
                if ($result['type'] === 'exact') {
                    return $result['location'];
                }

                if ($result['type'] === 'fuzzy' && $result['score'] > $highestScore) {
                    $highestScore = $result['score'];
                    $bestFuzzyMatch = $result['location'];
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return ($bestFuzzyMatch && $highestScore >= 2) ? $bestFuzzyMatch : null;
    }

    private function isValidExtension(string $filename): bool
    {
        foreach ($this->extensions as $ext) {
            if (str_ends_with($filename, $ext)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array{type: 'exact', location: array{file: string, line: int}}|array{type: 'fuzzy', score: float, location: array{file: string, line: int}}|array{type: 'none'}
     */
    private function searchInFile(string $filepath, string $searchSnippet, string $fullSnippet): array
    {
        $content = file_get_contents($filepath);
        if ($content === false) {
            return ['type' => 'none'];
        }

        $lines = explode("\n", $content);
        $parsedSnippet = $this->parseSnippet($fullSnippet);
        
        $bestLine = 0;
        $maxScore = 0;

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $replacedLine = preg_replace('/\s+/', ' ', $line);
            $cleanLine = is_string($replacedLine) ? trim($replacedLine) : '';

            if (str_contains($cleanLine, $searchSnippet)) {
                return [
                    'type' => 'exact',
                    'location' => ['file' => $filepath, 'line' => $lineNumber]
                ];
            }

            $score = $this->calculateFuzzyScore($cleanLine, $parsedSnippet);
            if ($score > $maxScore) {
                $maxScore = $score;
                $bestLine = $lineNumber;
            }
        }

        if ($maxScore > 0) {
            return [
                'type' => 'fuzzy',
                'score' => $maxScore,
                'location' => ['file' => $filepath, 'line' => $bestLine]
            ];
        }

        return ['type' => 'none'];
    }

    /**
     * @return array{tag: string|null, attributes: array<string, string>}
     */
    private function parseSnippet(string $snippet): array
    {
        preg_match('/<([a-zA-Z0-9]+)/', $snippet, $matches);
        $tagName = $matches[1] ?? null;

        preg_match_all('/([a-zA-Z0-9-]+)=["\']([^"\']*)["\']/', $snippet, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        return ['tag' => $tagName, 'attributes' => $attributes];
    }

    /**
     * @param array{tag: string|null, attributes: array<string, string>} $parsed
     */
    private function calculateFuzzyScore(string $line, array $parsed): float
    {
        if (!$parsed['tag'] || !str_contains(strtolower($line), '<' . strtolower($parsed['tag']))) {
            return 0;
        }

        $score = 1.0;

        if (in_array(strtolower($parsed['tag']), ['html', 'head', 'body', 'title'])) {
            $score += 1.0;
        }

        foreach ($parsed['attributes'] as $name => $value) {
            if (str_contains($line, "$name=\"$value\"") || str_contains($line, "$name='$value'")) {
                $score += 2.0;
            } elseif (str_contains($line, $name . '=')) {
                $score += 0.5;
            }
        }

        return $score;
    }
}
