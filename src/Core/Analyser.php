<?php

namespace YakNet\AccessibilityConsole\Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use YakNet\AccessibilityConsole\Core\Locator\PreciseElementLocator;
use YakNet\AccessibilityConsole\Parser\TemplatePreprocessor;
use YakNet\AccessibilityConsole\Rules\RuleLevels;

class Analyser
{
    private Config $config;
    private Scanner $scanner;
    private BaselineManager $baselineManager;
    private TemplatePreprocessor $templatePreprocessor;
    private PreciseElementLocator $elementLocator;
    private string $projectRoot;

    public function __construct(
        ?Config $config = null, 
        ?Scanner $scanner = null, 
        ?BaselineManager $baselineManager = null,
        ?TemplatePreprocessor $templatePreprocessor = null,
        ?PreciseElementLocator $elementLocator = null
    ) {
        $this->config = $config ?? new Config();
        $this->scanner = $scanner ?? $this->setupScanner();
        $this->baselineManager = $baselineManager ?? new BaselineManager();
        $this->templatePreprocessor = $templatePreprocessor ?? new TemplatePreprocessor();
        $this->elementLocator = $elementLocator ?? new PreciseElementLocator();
        
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
            
            $rawContent = @file_get_contents($file);
            if ($rawContent === false || trim($rawContent) === '') {
                continue;
            }

            // Preprocess templates (Blade, Twig, PHP) into scan-ready HTML preserving line numbers
            $scanContent = $this->templatePreprocessor->preprocess($rawContent, $file);
            
            // Scan file content
            try {
                $fileViolations = $this->scanner->scan($scanContent);
            } catch (\Throwable $e) {
                // Skip if parsing failed dramatically
                continue;
            }
            
            $relativeFile = $this->makePathRelative($file);
            
            foreach ($fileViolations as $violation) {
                // Find line and column number using high-precision locator
                if (!isset($violation->location['line']) || $violation->location['line'] <= 1) {
                    $location = $this->elementLocator->locate($rawContent, $violation->htmlSnippet);
                    $violation->location = [
                        'file' => $relativeFile,
                        'line' => $location['line'],
                        'column' => $location['column'],
                    ];
                } else {
                    $violation->location['file'] = $relativeFile;
                }
                
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
}
