<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\AI\GeminiFixer;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\BaselineManager;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\RuleLevels;
use YakNet\AccessibilityConsole\Reporting\ConsoleFormatter;
use YakNet\AccessibilityConsole\Reporting\GithubFormatter;
use YakNet\AccessibilityConsole\Reporting\JsonFormatter;
use YakNet\AccessibilityConsole\Reporting\XmlFormatter;
use YakNet\AccessibilityConsole\Reporting\MarkdownFormatter;
use YakNet\AccessibilityConsole\Reporting\CsvFormatter;
use YakNet\AccessibilityConsole\Reporting\GitLabCodeQualityFormatter;
use YakNet\AccessibilityConsole\Source\SourceLocator;

class ScanCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('scan')
            ->setAliases(['analyse'])
            ->setDescription('Scan local files, directories, or a URL for accessibility violations')
            ->addArgument('target', InputArgument::OPTIONAL, 'The URL, file, or directory path to scan')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration file (default: a11y.yaml)')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Accessibility scan level (1-9)')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (console, json, github)')
            ->addOption('generate-baseline', null, InputOption::VALUE_NONE, 'Generate a baseline file containing all current violations')
            ->addOption('baseline', null, InputOption::VALUE_REQUIRED, 'Path to the baseline file')
            ->addOption('no-progress', null, InputOption::VALUE_NONE, 'Disable progress bar')
            ->addOption('ai', null, InputOption::VALUE_NONE, 'Enable AI-powered fix suggestions')
            ->addOption('report', null, InputOption::VALUE_REQUIRED, 'Save report to file (html or json)')
            ->addOption('project-path', null, InputOption::VALUE_REQUIRED, 'Base path for source mapping (URL scans only)')
            ->addOption('crawl', null, InputOption::VALUE_NONE, 'Crawl internal links recursively (URL scans only)')
            ->addOption('depth', null, InputOption::VALUE_REQUIRED, 'Maximum crawling depth (URL scans only)', '3')
            ->addOption('max-pages', null, InputOption::VALUE_REQUIRED, 'Maximum pages to crawl (URL scans only)', '20')
            ->addOption('js', null, InputOption::VALUE_NONE, 'Enable JavaScript rendering using Headless Chrome (URL scans only)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // 1. Resolve configuration
        $configPathOption = $input->getOption('config');
        $configPath = is_string($configPathOption) ? $configPathOption : getcwd() . '/a11y.yaml';
        $config = Config::fromYaml($configPath);

        // 2. Resolve parameters (CLI options override config file)
        $levelOption = $input->getOption('level');
        $level = is_numeric($levelOption) ? (int)$levelOption : $config->getLevel();

        $formatOption = $input->getOption('format');
        $format = is_string($formatOption) ? $formatOption : $config->getFormat();

        $baselinePathOption = $input->getOption('baseline');
        $baselinePath = is_string($baselinePathOption) ? $baselinePathOption : $config->getBaselinePath();
        if (!$baselinePath && $input->getOption('generate-baseline')) {
            $baselinePath = 'a11y-baseline.json'; // default baseline filename
        }

        $target = $input->getArgument('target');
        $isUrl = is_string($target) && (str_starts_with($target, 'http://') || str_starts_with($target, 'https://'));

        // If target is a URL, run the legacy URL scanning / crawling logic
        if ($isUrl) {
            return $this->executeUrlScan($target, $level, $input, $output, $io);
        }

        // --- LOCAL DIRECTORY / FILE STATIC ANALYSIS ENGINE ---
        $io->title("Accessibility Console - Static Analysis");
        $io->writeln("<fg=gray>Configuration loaded from:</> <fg=cyan>$configPath</>");
        $io->writeln("<fg=gray>Rule Level:</> <fg=yellow>$level</>");

        // Resolve paths to scan
        $paths = $config->getPaths();
        if (is_string($target) && $target !== '') {
            $paths = [$target];
        }

        // Setup Analyser
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($level);
        
        // Filter rules based on config
        $rulesConfig = $config->getRulesConfig();
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
                } catch (\Throwable $e) {}
            }
        }

        foreach ($activeRules as $rule) {
            $scanner->addRule($rule);
        }

        // Setup Baseline Manager
        $baselineManager = new BaselineManager();
        if ($baselinePath && !$input->getOption('generate-baseline')) {
            $baselineManager->load(getcwd() . '/' . $baselinePath);
        }

        $analyser = new Analyser($config, $scanner, $baselineManager);

        // Progress bar setup
        $progressBar = null;
        $showProgress = !$input->getOption('no-progress') && $format === 'console';
        $onProgress = null;

        if ($showProgress) {
            $onProgress = function (string $file, int $index, int $total) use ($io, &$progressBar) {
                if ($progressBar === null) {
                    $progressBar = new ProgressBar($io, $total);
                    $progressBar->start();
                }
                $progressBar->setProgress($index);
                $progressBar->setMessage(basename($file), 'filename');
            };
        }

        // Run analysis
        $result = $analyser->analyse($paths, $onProgress);
        
        if ($progressBar) {
            $progressBar->finish();
            $io->newLine(2);
        }

        $violations = $result['violations'];
        $baselinedCount = $result['baselinedCount'];

        // AI Fix generation (if requested)
        if ($input->getOption('ai') && !empty($violations)) {
            $apiKey = $config->getGeminiApiKey();
            if ($apiKey) {
                $fixer = new GeminiFixer($apiKey);
                $io->comment("Requesting AI fix suggestions for violations...");
                foreach ($violations as $v) {
                    $suggestion = $fixer->suggestFix($v);
                    if ($suggestion) {
                        $v->fixSuggestion = $suggestion;
                    }
                }
            } else {
                $io->warning("GEMINI_API_KEY not found. AI suggestions skipped.");
            }
        }

        // Generate baseline if option is present
        if ($input->getOption('generate-baseline')) {
            if (!$baselinePath) {
                $io->error("No baseline path specified.");
                return Command::FAILURE;
            }
            $baselineFile = getcwd() . '/' . $baselinePath;
            
            // Re-run analysis without baseline filtering to catch all violations
            $unfilteredAnalyser = new Analyser($config, $scanner, new BaselineManager());
            $allResult = $unfilteredAnalyser->analyse($paths);
            $allViolations = $allResult['violations'];

            if ($baselineManager->generate($allViolations, $baselineFile)) {
                $io->success(sprintf("Baseline generated successfully with %d violations at: %s", count($allViolations), $baselinePath));
                return Command::SUCCESS;
            } else {
                $io->error("Failed to generate baseline file.");
                return Command::FAILURE;
            }
        }

        // Select formatter
        $formatter = new ConsoleFormatter();
        if ($format === 'json') {
            $formatter = new JsonFormatter();
        } elseif ($format === 'github') {
            $formatter = new GithubFormatter();
        } elseif ($format === 'xml') {
            $formatter = new XmlFormatter();
        } elseif ($format === 'markdown') {
            $formatter = new MarkdownFormatter();
        } elseif ($format === 'csv') {
            $formatter = new CsvFormatter();
        } elseif ($format === 'gitlab') {
            $formatter = new GitLabCodeQualityFormatter();
        }

        $exitCode = $formatter->format($violations, $baselinedCount, $io);

        if ($format === 'console') {
            $metrics = \YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator::calculate($violations);
            $io->section("Accessibility Ratings");
            $color = $metrics['score'] >= 90 ? 'green' : ($metrics['score'] >= 70 ? 'yellow' : 'red');
            $io->writeln(sprintf("Health Score: <fg=%s>%d/100</>", $color, $metrics['score']));
            $io->writeln(sprintf(" - Screen Reader / Visual Impact: <fg=cyan>%d%%</>", $metrics['visualImpact']));
            $io->writeln(sprintf(" - Keyboard / Motor Impact: <fg=cyan>%d%%</>", $metrics['motorImpact']));
            $io->writeln(sprintf(" - Cognitive / Distraction Impact: <fg=cyan>%d%%</>", $metrics['cognitiveImpact']));
            $io->newLine();
        }

        // Export report if --report is specified
        $reportPath = $input->getOption('report');
        if (is_string($reportPath) && $reportPath !== '') {
            $extension = pathinfo($reportPath, PATHINFO_EXTENSION);
            if ($extension === 'json') {
                $serialized = [];
                foreach ($violations as $v) {
                    $serialized[] = $v->toArray();
                }
                file_put_contents($reportPath, json_encode($serialized, JSON_PRETTY_PRINT));
            } else {
                $reporter = new \YakNet\AccessibilityConsole\Reporting\HtmlDashboardReporter();
                // Map violations into page format
                $mappedViolations = ['Static Analysis' => $violations];
                file_put_contents($reportPath, $reporter->render($mappedViolations, 'Static Analysis'));
            }
            $io->success("Report saved to: $reportPath");
        }

        return $exitCode;
    }

    /**
     * Legacy URL scanning and crawling logic
     */
    private function executeUrlScan(string $target, int $level, InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        $enableAi = (bool)$input->getOption('ai');
        $projectPathOption = $input->getOption('project-path');
        $projectPath = is_string($projectPathOption) ? $projectPathOption : getcwd();
        if ($projectPath === false) {
            $projectPath = '.';
        }

        $io->title("Accessibility Console - Scanning URL: $target (Level $level)");

        // 1. Setup Scanner
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($level);
        foreach ($rules as $rule) {
            $scanner->addRule($rule);
        }

        // 2. Fetch and Scan
        $crawl = (bool)$input->getOption('crawl');
        $useJs = (bool)$input->getOption('js');
        $chromeRenderer = $useJs ? new \YakNet\AccessibilityConsole\Core\ChromeRenderer() : null;
        $results = [];

        if ($crawl) {
            $depthOption = $input->getOption('depth');
            $depthLimit = is_numeric($depthOption) ? (int)$depthOption : 3;
            $maxPagesOption = $input->getOption('max-pages');
            $maxPages = is_numeric($maxPagesOption) ? (int)$maxPagesOption : 20;
            
            $io->comment("Starting recursive crawl from: $target (Max Depth: $depthLimit, Max Pages: $maxPages)");

            $parsedBase = parse_url($target);
            $baseHost = $parsedBase['host'] ?? '';

            $queue = [['url' => $target, 'depth' => 1]];
            $visited = [];

            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'allow_redirects' => true,
                'headers' => [
                    'User-Agent' => 'YakNet Accessibility Console Crawler/1.0',
                ]
            ]);

            while (!empty($queue) && count($visited) < $maxPages) {
                $current = array_shift($queue);
                
                $url = $current['url'];
                $depth = (int)$current['depth'];

                $cleanUrl = strtok($url, '#');
                if ($cleanUrl === false || isset($visited[$cleanUrl])) {
                    continue;
                }

                $visited[$cleanUrl] = true;
                $io->write("  <fg=gray>Crawling [Depth $depth]:</> <fg=cyan>$cleanUrl</>... ");

                try {
                    if ($chromeRenderer) {
                        $html = $chromeRenderer->render($cleanUrl);
                    } else {
                        $response = $client->request('GET', $cleanUrl);
                        $html = (string)$response->getBody();
                    }
                    $io->writeln("<fg=green>OK</>");
                    
                    $pageViolations = $scanner->scan($html);
                    $results[$cleanUrl] = $pageViolations;

                    if ($depth < $depthLimit) {
                        $html5 = new \Masterminds\HTML5(['disable_html_ns' => true]);
                        $doc = $html5->loadHTML($html);
                        $links = $doc->getElementsByTagName('a');
                        
                        foreach ($links as $link) {
                            $href = $link->getAttribute('href');
                            if (empty($href) || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                                continue;
                            }

                            try {
                                $baseUri = new \GuzzleHttp\Psr7\Uri($cleanUrl);
                                $relativeUri = new \GuzzleHttp\Psr7\Uri($href);
                                $absoluteUri = \GuzzleHttp\Psr7\UriResolver::resolve($baseUri, $relativeUri);
                                $absoluteUrl = (string)$absoluteUri;
                            } catch (\Throwable) {
                                continue;
                            }

                            $parsedAbsolute = parse_url($absoluteUrl);
                            $absoluteHost = $parsedAbsolute['host'] ?? '';

                            if ($absoluteHost === $baseHost) {
                                $cleanAbsolute = strtok($absoluteUrl, '#');
                                if ($cleanAbsolute !== false && !isset($visited[$cleanAbsolute])) {
                                    $inQueue = false;
                                    foreach ($queue as $q) {
                                        if ($q['url'] === $cleanAbsolute) {
                                            $inQueue = true;
                                            break;
                                        }
                                    }
                                    if (!$inQueue) {
                                        $queue[] = ['url' => $cleanAbsolute, 'depth' => $depth + 1];
                                    }
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $io->writeln("<fg=red>FAIL (" . $e->getMessage() . ")</>");
                }
            }
        } else {
            if ($chromeRenderer) {
                try {
                    $html = $chromeRenderer->render($target);
                } catch (\Throwable $e) {
                    $io->error("Failed JavaScript rendering: " . $e->getMessage());
                    return Command::FAILURE;
                }
            } else {
                $html = @file_get_contents($target);
            }
            
            if ($html === false || $html === '') {
                $io->error("Could not read target URL: $target");
                return Command::FAILURE;
            }
            $violations = $scanner->scan($html);
            $results[$target] = $violations;
        }

        $totalViolations = 0;
        foreach ($results as $url => $violations) {
            $totalViolations += count($violations);
        }

        if ($totalViolations === 0) {
            $io->success("No violations found!");
            return Command::SUCCESS;
        }

        $io->section("Found a total of $totalViolations violations across " . count($results) . " pages");

        $locator = new SourceLocator($projectPath);
        $config = new Config();
        $fixer = $enableAi ? new GeminiFixer($config->getGeminiApiKey()) : null;

        foreach ($results as $url => $violations) {
            if (empty($violations)) {
                continue;
            }
            $io->section("Page: $url (" . count($violations) . " violations)");

            foreach ($violations as $violation) {
                $io->writeln("<fg=white;bg=red;options=bold> {$violation->ruleId} </>\t<fg=red>{$violation->message}</>");
                $io->writeln("  <fg=gray>Snippet:</> <fg=yellow>" . htmlspecialchars($violation->htmlSnippet) . "</>");

                $location = $locator->locate($violation->htmlSnippet);
                if ($location) {
                    $io->writeln("  <fg=gray>Source:</>  <fg=cyan>{$location['file']}:{$location['line']}</>");
                    $violation->location = $location;
                }

                if ($enableAi && $fixer) {
                    $io->write("  <fg=gray>AI Fix Suggestion:</> ");
                    $suggestion = $fixer->suggestFix($violation);
                    if ($suggestion) {
                        $violation->fixSuggestion = $suggestion;
                        $io->writeln("<fg=green>" . htmlspecialchars($suggestion) . "</>");
                    } else {
                        $io->writeln("<fg=red>Could not generate suggestion (check API key)</>");
                    }
                }
                $io->writeln("");
            }
        }

        $reportPath = $input->getOption('report');
        if (is_string($reportPath) && $reportPath !== '') {
            $extension = pathinfo($reportPath, PATHINFO_EXTENSION);
            if ($extension === 'json') {
                $serialized = [];
                foreach ($results as $url => $violations) {
                    $serialized[$url] = array_map(fn($v) => $v->toArray(), $violations);
                }
                file_put_contents($reportPath, json_encode($serialized, JSON_PRETTY_PRINT));
            } else {
                $reporter = new \YakNet\AccessibilityConsole\Reporting\HtmlDashboardReporter();
                file_put_contents($reportPath, $reporter->render($results, $target));
            }
            $io->success("Report saved to: $reportPath");
        }

        return Command::FAILURE;
    }
}
