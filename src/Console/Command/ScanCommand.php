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
use YakNet\AccessibilityConsole\Analytics\HistoryTracker;
use YakNet\AccessibilityConsole\Cache\ScanCache;
use YakNet\AccessibilityConsole\Compliance\VpatReportGenerator;
use YakNet\AccessibilityConsole\Core\Analyser;
use YakNet\AccessibilityConsole\Core\BaselineManager;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Notifications\DiscordNotifier;
use YakNet\AccessibilityConsole\Notifications\GenericWebhookNotifier;
use YakNet\AccessibilityConsole\Notifications\MicrosoftTeamsNotifier;
use YakNet\AccessibilityConsole\Notifications\NotificationDispatcher;
use YakNet\AccessibilityConsole\Notifications\NotificationPayload;
use YakNet\AccessibilityConsole\Notifications\SlackNotifier;
use YakNet\AccessibilityConsole\Policy\PolicyEngine;
use YakNet\AccessibilityConsole\Rules\RuleExecutionStats;
use YakNet\AccessibilityConsole\Rules\RuleLevels;
use YakNet\AccessibilityConsole\Reporting\CheckstyleFormatter;
use YakNet\AccessibilityConsole\Reporting\ConsoleFormatter;
use YakNet\AccessibilityConsole\Reporting\GithubFormatter;
use YakNet\AccessibilityConsole\Reporting\JsonFormatter;
use YakNet\AccessibilityConsole\Reporting\XmlFormatter;
use YakNet\AccessibilityConsole\Reporting\MarkdownFormatter;
use YakNet\AccessibilityConsole\Reporting\CsvFormatter;
use YakNet\AccessibilityConsole\Reporting\GitLabCodeQualityFormatter;
use YakNet\AccessibilityConsole\Reporting\SarifFormatter;
use YakNet\AccessibilityConsole\Reporting\JunitFormatter;
use YakNet\AccessibilityConsole\Reporting\PdfReportGenerator;
use YakNet\AccessibilityConsole\Reporting\ExcelReportExporter;
use YakNet\AccessibilityConsole\Badges\BadgeGenerator;
use YakNet\AccessibilityConsole\Presets\PresetRegistry;
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
            ->addOption('preset', 'p', InputOption::VALUE_REQUIRED, 'Use a compliance preset (section508, en301549, eaa2025, bitv2, wcag22-aa, wcag22-aaa, government, healthcare, banking, education, ecommerce)')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (console, json, github, xml, markdown, csv, gitlab, sarif, junit, checkstyle)')
            ->addOption('min-score', null, InputOption::VALUE_REQUIRED, 'Minimum required accessibility health score (0-100) to pass the quality gate')
            ->addOption('max-warnings', null, InputOption::VALUE_REQUIRED, 'Maximum allowed warnings before failing the quality gate')
            ->addOption('fail-on', null, InputOption::VALUE_REQUIRED, 'Minimum severity to fail on (error, warning, info)')
            ->addOption('generate-baseline', null, InputOption::VALUE_NONE, 'Generate a baseline file containing all current violations')
            ->addOption('baseline', null, InputOption::VALUE_REQUIRED, 'Path to the baseline file')
            ->addOption('no-progress', null, InputOption::VALUE_NONE, 'Disable progress bar')
            ->addOption('ai', null, InputOption::VALUE_NONE, 'Enable AI-powered fix suggestions')
            ->addOption('report', null, InputOption::VALUE_REQUIRED, 'Save report to file (html, json, sarif, xml, junit, checkstyle, pdf, xlsx)')
            ->addOption('vpat', null, InputOption::VALUE_REQUIRED, 'Export a VPAT 2.5 Conformance Report to file (.md, .html, or .json)')
            ->addOption('save-history', null, InputOption::VALUE_REQUIRED, 'Append scan snapshot to a JSON history file for trend tracking')
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, 'Path to incremental content-hash cache file (e.g. .a11y-cache.json)')
            ->addOption('clear-cache', null, InputOption::VALUE_NONE, 'Clear the incremental scan cache before scanning')
            ->addOption('profile', null, InputOption::VALUE_NONE, 'Display rule execution timing profile (top slowest rules)')
            ->addOption('webhook-slack', null, InputOption::VALUE_REQUIRED, 'Send scan summary to a Slack Incoming Webhook URL')
            ->addOption('webhook-discord', null, InputOption::VALUE_REQUIRED, 'Send scan summary to a Discord Webhook URL')
            ->addOption('webhook-teams', null, InputOption::VALUE_REQUIRED, 'Send scan summary to a Microsoft Teams Webhook URL')
            ->addOption('webhook-url', null, InputOption::VALUE_REQUIRED, 'Send JSON payload to a generic webhook URL')
            ->addOption('badge', null, InputOption::VALUE_REQUIRED, 'Save SVG accessibility health badge to file (e.g. a11y-badge.svg)')
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

        $presetOption = $input->getOption('preset');
        $presetName = (is_string($presetOption) && $presetOption !== '') ? $presetOption : $config->getPreset();
        $activePreset = null;
        if (is_string($presetName) && $presetName !== '') {
            $presetRegistry = new PresetRegistry();
            $activePreset = $presetRegistry->get($presetName);
            if ($activePreset !== null) {
                $level = $activePreset->getLevel();
            } else {
                $io->warning("Preset '{$presetName}' not found. Using default rules.");
            }
        }

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
        if ($format === 'console') {
            $io->title("Accessibility Console - Static Analysis");
            $io->writeln("<fg=gray>Configuration loaded from:</> <fg=cyan>$configPath</>");
            $io->writeln("<fg=gray>Rule Level:</> <fg=yellow>$level</>");
            if ($activePreset !== null) {
                $io->writeln("<fg=gray>Compliance Preset:</> <fg=magenta>" . $activePreset->getName() . " (" . $activePreset->getDescription() . ")</>");
            }
        }

        // Resolve paths to scan
        $paths = $config->getPaths();
        if (is_string($target) && $target !== '') {
            $paths = [$target];
        }

        // Setup Analyser
        $scanner = new Scanner();
        $enableProfile = (bool)$input->getOption('profile');
        $executionStats = $enableProfile ? new RuleExecutionStats() : null;
        if ($executionStats !== null) {
            $scanner->getEngine()->setStats($executionStats);
        }

        $rules = RuleLevels::getRulesForLevel($level);
        
        // Filter rules based on config and active preset
        $rulesConfig = $config->getRulesConfig();
        $excludedIds = $rulesConfig['exclude'];
        if ($activePreset !== null) {
            $excludedIds = array_values(array_unique(array_merge($excludedIds, $activePreset->getExcludedRules())));
        }

        $activeRules = [];
        foreach ($rules as $rule) {
            $ruleId = $rule->getId();
                
            if (in_array($ruleId, $excludedIds, true)) {
                continue;
            }
            $activeRules[$ruleId] = $rule;
        }

        // Ensure preset-included priority rules are active even if they belong to a higher scan level
        if ($activePreset !== null && !empty($activePreset->getIncludedRules())) {
            $allRulesById = [];
            foreach (RuleLevels::getRulesForLevel(9) as $candidateRule) {
                $cid = $candidateRule->getId();
                $allRulesById[$cid] = $candidateRule;
            }
            foreach ($activePreset->getIncludedRules() as $priorityRuleId) {
                if (!isset($activeRules[$priorityRuleId]) && isset($allRulesById[$priorityRuleId]) && !in_array($priorityRuleId, $excludedIds, true)) {
                    $activeRules[$priorityRuleId] = $allRulesById[$priorityRuleId];
                }
            }
        }

        foreach ($rulesConfig['include'] as $customRuleClass) {
            if (class_exists($customRuleClass)) {
                try {
                    $reflection = new \ReflectionClass($customRuleClass);
                    if ($reflection->isInstantiable()) {
                        $rule = $reflection->newInstance();
                        $ruleId = method_exists($rule, 'getId')
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

        // Setup Incremental Scan Cache (if --cache is passed)
        $scanCache = null;
        $cachePathOption = $input->getOption('cache');
        if (is_string($cachePathOption) && $cachePathOption !== '') {
            $rulesSig = hash('sha256', implode('|', array_keys($activeRules)));
            $scanCache = new ScanCache($cachePathOption, $rulesSig);
            if ($input->getOption('clear-cache')) {
                $scanCache->clear();
            } else {
                $scanCache->load();
            }
        }

        $analyser = new Analyser($config, $scanner, $baselineManager, null, null, null, $scanCache);

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

        // Apply governance policy severity overrides before formatting & scoring
        $policyEngine = new PolicyEngine($config->getPolicyConfig());
        $policyEngine->applySeverityOverrides($violations);

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
        } elseif ($format === 'sarif') {
            $formatter = new SarifFormatter();
        } elseif ($format === 'junit') {
            $formatter = new JunitFormatter();
        } elseif ($format === 'checkstyle') {
            $formatter = new CheckstyleFormatter();
        }

        $exitCode = $formatter->format($violations, $baselinedCount, $io);

        $metrics = \YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator::calculate($violations);
        $score = $metrics['score'];
        $suppressedLog = $analyser->getSuppressedAuditLog();

        if ($format === 'console') {
            $io->section("Accessibility Ratings");
            $color = $score >= 90 ? 'green' : ($score >= 70 ? 'yellow' : 'red');
            $io->writeln(sprintf("Health Score: <fg=%s>%d/100</>", $color, $score));
            $io->writeln(sprintf(" - Screen Reader / Visual Impact: <fg=cyan>%d%%</>", $metrics['visualImpact']));
            $io->writeln(sprintf(" - Keyboard / Motor Impact: <fg=cyan>%d%%</>", $metrics['motorImpact']));
            $io->writeln(sprintf(" - Cognitive / Distraction Impact: <fg=cyan>%d%%</>", $metrics['cognitiveImpact']));
            if (!empty($suppressedLog)) {
                $io->writeln(sprintf(" - Inline Suppressed Violations: <fg=yellow>%d</>", count($suppressedLog)));
            }
            $io->newLine();
        }

        // Execution profiling summary (--profile)
        if ($executionStats !== null && $format === 'console') {
            $slowest = $executionStats->getSlowestRules(10);
            if (!empty($slowest)) {
                $io->section('Rule Execution Profile (Top 10 Slowest)');
                $profileTable = new \Symfony\Component\Console\Helper\Table($output);
                $profileTable->setHeaders(['Rule ID', 'Invocations', 'Total (ms)', 'Avg (ms)', 'Violations']);
                foreach ($slowest as $rId => $stat) {
                    $profileTable->addRow([
                        $rId,
                        (string)$stat['invocations'],
                        sprintf('%.3f', $stat['total_time_ms']),
                        sprintf('%.4f', $stat['avg_time_ms']),
                        (string)$stat['violations'],
                    ]);
                }
                $profileTable->render();
                $io->newLine();
            }
        }

        // Quality Gate & Policy Engine evaluation
        $minScoreOption = $input->getOption('min-score');
        $maxWarningsOption = $input->getOption('max-warnings');
        $failOnOption = $input->getOption('fail-on');

        $errorCount = 0;
        $warningCount = 0;
        foreach ($violations as $v) {
            if ($v->severity === Severity::CRITICAL || $v->severity === Severity::ERROR) {
                $errorCount++;
            } elseif ($v->severity === Severity::WARNING) {
                $warningCount++;
            }
        }

        $qualityGateFailed = false;
        /** @var string[] $gateFailures */
        $gateFailures = [];

        if ($policyEngine->hasPolicies()) {
            $policyResult = $policyEngine->evaluate($violations);
            if (!$policyResult->passed) {
                $qualityGateFailed = true;
                $gateFailures = array_merge($gateFailures, $policyResult->failures);
            }
        }

        if ($minScoreOption !== null && is_numeric($minScoreOption)) {
            $minScore = (int)$minScoreOption;
            if ($score < $minScore) {
                $qualityGateFailed = true;
                $gateFailures[] = sprintf("Accessibility score (%d/100) is lower than the minimum required threshold of %d/100.", $score, $minScore);
            }
        }

        if ($maxWarningsOption !== null && is_numeric($maxWarningsOption)) {
            $maxWarnings = (int)$maxWarningsOption;
            if ($warningCount > $maxWarnings) {
                $qualityGateFailed = true;
                $gateFailures[] = sprintf("Warning count (%d) exceeds the maximum allowed limit of %d.", $warningCount, $maxWarnings);
            }
        }

        if (is_string($failOnOption)) {
            $failOn = strtolower($failOnOption);
            if ($failOn === 'error') {
                $exitCode = ($errorCount > 0) ? Command::FAILURE : Command::SUCCESS;
            } elseif ($failOn === 'warning') {
                $exitCode = ($errorCount > 0 || $warningCount > 0) ? Command::FAILURE : Command::SUCCESS;
            }
        }

        if ($qualityGateFailed) {
            $io->newLine();
            $io->error("CI/CD Quality Gate Failed:\n- " . implode("\n- ", $gateFailures));
            $exitCode = Command::FAILURE;
        }

        // Export report if --report is specified
        $reportPath = $input->getOption('report');
        if (is_string($reportPath) && $reportPath !== '') {
            $this->exportReport($reportPath, $violations, 'Static Analysis', $io);
        }

        // Export VPAT 2.5 report if --vpat is specified
        $vpatPath = $input->getOption('vpat');
        if (is_string($vpatPath) && $vpatPath !== '') {
            $vpatGenerator = new VpatReportGenerator();
            $vpatExt = strtolower(pathinfo($vpatPath, PATHINFO_EXTENSION));
            $vpatContent = match ($vpatExt) {
                'html', 'htm' => $vpatGenerator->renderHtml($violations, basename((string)getcwd()) ?: 'Web Application'),
                'json' => $vpatGenerator->renderJson($violations, basename((string)getcwd()) ?: 'Web Application'),
                default => $vpatGenerator->renderMarkdown($violations, basename((string)getcwd()) ?: 'Web Application'),
            };
            file_put_contents($vpatPath, $vpatContent);
            $io->success("VPAT 2.5 Conformance Report saved to: $vpatPath");
        }

        // Save history snapshot if --save-history is specified
        $historyPath = $input->getOption('save-history');
        if (is_string($historyPath) && $historyPath !== '') {
            $tracker = new HistoryTracker();
            $tracker->record($violations, $historyPath, implode(', ', $paths), $activePreset?->getName());
            $trend = $tracker->computeTrend($tracker->load($historyPath));
            if ($format === 'console') {
                $deltaStr = $trend['score_delta'] > 0 ? '+' . $trend['score_delta'] : (string)$trend['score_delta'];
                $io->note(sprintf('History saved (%d total runs, trend: %s, delta: %s).', $trend['runs'], $trend['trend'], $deltaStr));
            }
        }

        // Export SVG badge if --badge is specified
        $badgePath = $input->getOption('badge');
        if (is_string($badgePath) && $badgePath !== '') {
            file_put_contents($badgePath, BadgeGenerator::generateScoreBadge($score));
            $io->success("SVG Badge saved to: $badgePath");
        }

        // Dispatch webhook notifications (CLI flags or a11y.yaml notifications block)
        $this->dispatchNotifications($input, $config, implode(', ', $paths), $score, $violations, $errorCount, $warningCount);

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
            $allViolations = [];
            foreach ($results as $urlViolations) {
                $allViolations = array_merge($allViolations, $urlViolations);
            }
            $this->exportReport($reportPath, $allViolations, $target, $io, $results);
        }

        return Command::FAILURE;
    }

    /**
     * Export violations to various file formats (HTML, JSON, SARIF, XML/JUnit, PDF, Excel).
     *
     * @param string $reportPath
     * @param \YakNet\AccessibilityConsole\Core\Violation[] $violations
     * @param string $targetName
     * @param SymfonyStyle $io
     * @param array<string, \YakNet\AccessibilityConsole\Core\Violation[]>|null $pageResults
     */
    private function exportReport(
        string $reportPath,
        array $violations,
        string $targetName,
        SymfonyStyle $io,
        ?array $pageResults = null
    ): void {
        $extension = strtolower(pathinfo($reportPath, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            if ($pageResults !== null) {
                $serialized = [];
                foreach ($pageResults as $url => $pageViolations) {
                    $serialized[$url] = array_map(fn($v) => $v->toArray(), $pageViolations);
                }
            } else {
                $serialized = [];
                foreach ($violations as $v) {
                    $serialized[] = $v->toArray();
                }
            }
            file_put_contents($reportPath, (string)json_encode($serialized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } elseif ($extension === 'sarif') {
            $sarifFormatter = new SarifFormatter();
            file_put_contents($reportPath, $sarifFormatter->render($violations));
        } elseif ($extension === 'checkstyle') {
            $checkstyleFormatter = new CheckstyleFormatter();
            file_put_contents($reportPath, $checkstyleFormatter->render($violations));
        } elseif ($extension === 'xml' || $extension === 'junit') {
            $junitFormatter = new JunitFormatter();
            file_put_contents($reportPath, $junitFormatter->render($violations));
        } elseif ($extension === 'pdf') {
            $metrics = \YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator::calculate($violations);
            $serialized = [];
            foreach ($violations as $v) {
                $serialized[] = $v->toArray();
            }
            $scanResult = [
                'score' => $metrics['score'],
                'violations' => $serialized,
            ];
            file_put_contents($reportPath, PdfReportGenerator::generatePdf($scanResult, $targetName));
        } elseif ($extension === 'xlsx' || $extension === 'xls') {
            $metrics = \YakNet\AccessibilityConsole\Core\Metrics\AccessibilityScoreCalculator::calculate($violations);
            $serialized = [];
            foreach ($violations as $v) {
                $serialized[] = $v->toArray();
            }
            $scanResult = [
                'score' => $metrics['score'],
                'violations' => $serialized,
            ];
            file_put_contents($reportPath, ExcelReportExporter::exportExcel($scanResult, $targetName));
        } else {
            $reporter = new \YakNet\AccessibilityConsole\Reporting\HtmlDashboardReporter();
            $mapped = $pageResults ?? [$targetName => $violations];
            file_put_contents($reportPath, $reporter->render($mapped, $targetName));
        }

        $io->success("Report saved to: $reportPath");
    }

    /**
     * Dispatch configured webhook notifications (Slack, Discord, Microsoft Teams, generic JSON webhook).
     *
     * @param \YakNet\AccessibilityConsole\Core\Violation[] $violations
     */
    private function dispatchNotifications(
        InputInterface $input,
        Config $config,
        string $target,
        int $score,
        array $violations,
        int $errorCount,
        int $warningCount
    ): void {
        $notifConfig = $config->getNotificationsConfig();
        $dispatcher = new NotificationDispatcher();

        $slackUrl = $input->getOption('webhook-slack');
        if (!is_string($slackUrl) || $slackUrl === '') {
            $slackUrl = is_string($notifConfig['slack_webhook'] ?? null) ? $notifConfig['slack_webhook'] : '';
        }
        if ($slackUrl !== '') {
            $dispatcher->addNotifier('slack', new SlackNotifier($slackUrl));
        }

        $discordUrl = $input->getOption('webhook-discord');
        if (!is_string($discordUrl) || $discordUrl === '') {
            $discordUrl = is_string($notifConfig['discord_webhook'] ?? null) ? $notifConfig['discord_webhook'] : '';
        }
        if ($discordUrl !== '') {
            $dispatcher->addNotifier('discord', new DiscordNotifier($discordUrl));
        }

        $teamsUrl = $input->getOption('webhook-teams');
        if (!is_string($teamsUrl) || $teamsUrl === '') {
            $teamsUrl = is_string($notifConfig['teams_webhook'] ?? null) ? $notifConfig['teams_webhook'] : '';
        }
        if ($teamsUrl !== '') {
            $dispatcher->addNotifier('teams', new MicrosoftTeamsNotifier($teamsUrl));
        }

        $genericUrl = $input->getOption('webhook-url');
        if (!is_string($genericUrl) || $genericUrl === '') {
            $genericUrl = is_string($notifConfig['webhook_url'] ?? null) ? $notifConfig['webhook_url'] : '';
        }
        $secret = is_string($notifConfig['webhook_secret'] ?? null) ? $notifConfig['webhook_secret'] : null;
        if ($genericUrl !== '') {
            $dispatcher->addNotifier('webhook', new GenericWebhookNotifier($genericUrl, $secret));
        }

        if (!$dispatcher->hasNotifiers()) {
            return;
        }

        $topViolations = array_map(
            static fn($v): array => $v->toArray(),
            array_slice($violations, 0, 5)
        );

        $payload = new NotificationPayload(
            target: $target,
            healthScore: $score,
            totalViolations: count($violations),
            errorCount: $errorCount,
            warningCount: $warningCount,
            topViolations: $topViolations
        );

        $dispatcher->dispatch($payload);
    }
}

