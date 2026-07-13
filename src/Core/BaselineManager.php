<?php

namespace YakNet\AccessibilityConsole\Core;

class BaselineManager
{
    /** @var array<string, bool> */
    private array $ignoredKeys = [];

    public function load(string $baselinePath): bool
    {
        if (!file_exists($baselinePath) || !is_readable($baselinePath)) {
            return false;
        }

        $content = file_get_contents($baselinePath);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['violations']) || !is_array($data['violations'])) {
            return false;
        }

        foreach ($data['violations'] as $item) {
            if (isset($item['file'], $item['ruleId'], $item['htmlSnippet'])) {
                $key = $this->calculateKey($item['file'], $item['ruleId'], $item['htmlSnippet']);
                $this->ignoredKeys[$key] = true;
            }
        }

        return true;
    }

    /**
     * @param Violation[] $violations
     */
    public function generate(array $violations, string $baselinePath): bool
    {
        $serialized = [
            'generator' => 'YakNet Accessibility Console',
            'violations' => []
        ];

        foreach ($violations as $v) {
            $file = $v->location['file'] ?? '';
            $serialized['violations'][] = [
                'file' => $file,
                'ruleId' => $v->ruleId,
                'htmlSnippet' => $v->htmlSnippet
            ];
        }

        // Sort baseline violations by file and ruleId for cleaner git diffs
        usort($serialized['violations'], function ($a, $b) {
            $fileCmp = strcmp($a['file'], $b['file']);
            if ($fileCmp !== 0) {
                return $fileCmp;
            }
            return strcmp($a['ruleId'], $b['ruleId']);
        });

        $json = json_encode($serialized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $dir = dirname($baselinePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($baselinePath, $json) !== false;
    }

    public function isIgnored(string $file, string $ruleId, string $htmlSnippet): bool
    {
        $key = $this->calculateKey($file, $ruleId, $htmlSnippet);
        return isset($this->ignoredKeys[$key]);
    }

    private function calculateKey(string $file, string $ruleId, string $htmlSnippet): string
    {
        // Normalize snippet using fuzzy baseline matcher
        $normalizedSnippet = \YakNet\AccessibilityConsole\Core\Baseline\FuzzyBaselineMatcher::normalize($htmlSnippet);
        if ($normalizedSnippet === '') {
            $normalizedSnippet = trim($htmlSnippet);
        }

        // Normalize file separator to forward slashes for cross-platform compatibility
        $normalizedFile = str_replace('\\', '/', $file);

        return md5($normalizedFile . '::' . $ruleId . '::' . $normalizedSnippet);
    }
}
