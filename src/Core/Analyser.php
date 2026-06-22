<?php

namespace YakNet\AccessibilityConsole\Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class Analyser
{
    private Config $config;
    private Scanner $scanner;
    private BaselineManager $baselineManager;
    private string $projectRoot;

    public function __construct(?Config $config = null, ?Scanner $scanner = null, ?BaselineManager $baselineManager = null)
    {
        $this->config = $config ?? new Config();
        $this->scanner = $scanner ?? $this->setupScanner();
        $this->baselineManager = $baselineManager ?? new BaselineManager();
        
        $cwd = getcwd();
        $this->projectRoot = $cwd ? str_replace('\\', '/', realpath($cwd) ?: $cwd) : '.';
        
        if ($baselineManager === null) {
            $baselinePath = $this->config->getBaselinePath();
            if ($baselinePath) {
                $this->baselineManager->load($this->projectRoot . '/' . $baselinePath);
            }
        }
    }

    private function setupScanner(): Scanner
    {
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($this->config->getLevel());
        $rulesConfig = $this->config->getRulesConfig();

        $activeRules = [];
        foreach ($rules as $rule) {
            $ruleId = ($rule instanceof \YakNet\AccessibilityConsole\Rules\RuleInterface) 
                ? $rule->getId() 
                : (new \ReflectionClass($rule))->getShortName();
                
            if (in_array($ruleId, $rulesConfig['exclude'], true)) {
                continue;
            }
            $activeRules[$ruleId] = $rule;
        }

        // Include custom rules
        foreach ($rulesConfig['include'] as $customRuleClass) {
            if (class_exists($customRuleClass)) {
                try {
                    $reflection = new \ReflectionClass($customRuleClass);
                    if ($reflection->isInstantiable()) {
                        $rule = $reflection->newInstance();
                        $ruleId = ($rule instanceof \YakNet\AccessibilityConsole\Rules\RuleInterface) 
                            ? $rule->getId() 
                            : $reflection->getShortName();
                        $activeRules[$ruleId] = $rule;
                    }
                } catch (\Throwable $e) {
                    // Ignore instantiation failures
                }
            }
        }

        foreach ($activeRules as $rule) {
            $scanner->addRule($rule);
        }

        return $scanner;
    }

    /**
     * Analyse files and return a list of violations.
     *
     * @param array<int, string>|null $pathsOverride
     * @param callable|null $onProgress Function called for progress tracking: fn(string $file, int $index, int $total)
     * @return array{violations: Violation[], baselinedCount: int}
     */
    public function analyse(?array $pathsOverride = null, ?callable $onProgress = null): array
    {
        $paths = $pathsOverride ?? $this->config->getPaths();
        $excludePaths = $this->config->getExcludePaths();
        
        // Resolve all files to scan
        $filesToScan = [];
        foreach ($paths as $path) {
            if (is_file($path)) {
                $filesToScan[] = str_replace('\\', '/', realpath($path) ?: $path);
            } elseif (is_dir($path)) {
                $files = $this->findFiles($path, $excludePaths);
                $filesToScan = array_merge($filesToScan, $files);
            }
        }
        
        $filesToScan = array_unique($filesToScan);
        $totalFiles = count($filesToScan);
        
        $violations = [];
        $baselinedCount = 0;
        
        foreach ($filesToScan as $index => $file) {
            if ($onProgress) {
                $onProgress($file, $index + 1, $totalFiles);
            }
            
            $content = @file_get_contents($file);
            if ($content === false || trim($content) === '') {
                continue;
            }
            
            // Scan file content
            try {
                $fileViolations = $this->scanner->scan($content);
            } catch (\Throwable $e) {
                // Skip if parsing failed dramatically
                continue;
            }
            
            $relativeFile = $this->makePathRelative($file);
            
            foreach ($fileViolations as $violation) {
                // Find line number using line-by-line helper
                $line = $this->locateLineInContent($content, $violation->htmlSnippet);
                
                // Store location in violation
                $violation->location = [
                    'file' => $relativeFile,
                    'line' => $line
                ];
                
                // Check if ignored in baseline
                if ($this->baselineManager->isIgnored($relativeFile, $violation->ruleId, $violation->htmlSnippet)) {
                    $baselinedCount++;
                } else {
                    $violations[] = $violation;
                }
            }
        }
        
        return [
            'violations' => $violations,
            'baselinedCount' => $baselinedCount
        ];
    }

    public function getBaselineManager(): BaselineManager
    {
        return $this->baselineManager;
    }

    public function makePathRelative(string $absolutePath): string
    {
        $absolutePath = str_replace('\\', '/', realpath($absolutePath) ?: $absolutePath);
        if ($this->projectRoot !== '.' && str_starts_with($absolutePath, $this->projectRoot)) {
            $relative = ltrim(substr($absolutePath, strlen($this->projectRoot)), '/');
            return $relative === '' ? '.' : $relative;
        }
        return $absolutePath;
    }

    /**
     * @param array<int, string> $excludePaths
     * @return array<int, string>
     */
    private function findFiles(string $dir, array $excludePaths): array
    {
        $realDir = realpath($dir);
        if ($realDir === false) {
            return [];
        }

        $files = [];
        try {
            $directory = new RecursiveDirectoryIterator($realDir, \FilesystemIterator::SKIP_DOTS);
            $filter = new \RecursiveCallbackFilterIterator($directory, function ($current) use ($excludePaths) {
                /** @var SplFileInfo $current */
                $filename = $current->getFilename();
                $pathname = str_replace('\\', '/', $current->getPathname());
                
                // Exclude common directories/files
                foreach ($excludePaths as $exclude) {
                    $normalizedExclude = str_replace('\\', '/', $exclude);
                    if ($filename === $normalizedExclude || str_contains($pathname, '/' . $normalizedExclude . '/')) {
                        return false;
                    }
                }
                return true;
            });
            
            $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);
            
            $extensions = ['php', 'html', 'twig', 'blade.php'];
            
            foreach ($iterator as $file) {
                /** @var SplFileInfo $file */
                if ($file->isDir()) {
                    continue;
                }
                
                $filename = $file->getFilename();
                $isValidExt = false;
                foreach ($extensions as $ext) {
                    if (str_ends_with($filename, '.' . $ext) || ($ext === 'blade.php' && str_ends_with($filename, '.blade.php'))) {
                        $isValidExt = true;
                        break;
                    }
                }
                
                if ($isValidExt) {
                    $files[] = str_replace('\\', '/', $file->getPathname());
                }
            }
        } catch (\Throwable $e) {
            // Ignore directory iteration errors
        }
        
        return $files;
    }

    private function locateLineInContent(string $content, string $snippet): int
    {
        $replaced = preg_replace('/\s+/', ' ', $snippet);
        $cleanSnippet = is_string($replaced) ? trim($replaced) : '';
        if ($cleanSnippet === '') {
            return 1;
        }

        $searchSnippet = mb_strlen($cleanSnippet) > 150 ? mb_substr($cleanSnippet, 0, 150) : $cleanSnippet;
        $lines = explode("\n", $content);
        
        // 1. Exact match pass
        foreach ($lines as $index => $line) {
            $replacedLine = preg_replace('/\s+/', ' ', $line);
            $cleanLine = is_string($replacedLine) ? trim($replacedLine) : '';
            if (str_contains($cleanLine, $searchSnippet)) {
                return $index + 1;
            }
        }

        // 2. Parse snippet to find tag and attributes for fuzzy match pass
        preg_match('/<([a-zA-Z0-9]+)/', $snippet, $matches);
        $tagName = $matches[1] ?? null;
        if (!$tagName) {
            return 1;
        }

        preg_match_all('/([a-zA-Z0-9-]+)=["\']([^"\']*)["\']/', $snippet, $attrMatches, PREG_SET_ORDER);
        $attributes = [];
        foreach ($attrMatches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        $bestLine = 1;
        $maxScore = 0;

        foreach ($lines as $index => $line) {
            $replacedLine = preg_replace('/\s+/', ' ', $line);
            $cleanLine = is_string($replacedLine) ? trim($replacedLine) : '';
            
            if (!str_contains(strtolower($cleanLine), '<' . strtolower($tagName))) {
                continue;
            }

            $score = 1.0;
            if (in_array(strtolower($tagName), ['html', 'head', 'body', 'title'], true)) {
                $score += 1.0;
            }

            foreach ($attributes as $name => $value) {
                if (str_contains($cleanLine, "$name=\"$value\"") || str_contains($cleanLine, "$name='$value'")) {
                    $score += 2.0;
                } elseif (str_contains($cleanLine, $name . '=')) {
                    $score += 0.5;
                }
            }

            if ($score > $maxScore) {
                $maxScore = $score;
                $bestLine = $index + 1;
            }
        }

        return $bestLine;
    }
}
