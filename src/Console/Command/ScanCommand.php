<?php

namespace YakNet\AccessibilityConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\AI\GeminiFixer;
use YakNet\AccessibilityConsole\Core\Config;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\StandardRuleSet;
use YakNet\AccessibilityConsole\Rules\RuleLevels;
use YakNet\AccessibilityConsole\Source\SourceLocator;

class ScanCommand extends Command
{
    protected static ?string $defaultName = 'scan';

    protected function configure(): void
    {
        $this
            ->setName('scan')
            ->setDescription('Scan a URL or file for accessibility violations')
            ->addArgument('target', InputArgument::REQUIRED, 'The URL or file path to scan')
            ->addOption('ai', null, InputOption::VALUE_NONE, 'Enable AI-powered fix suggestions')
            ->addOption('report', null, InputOption::VALUE_REQUIRED, 'Save report to file (html or json)')
            ->addOption('project-path', null, InputOption::VALUE_REQUIRED, 'Base path for source mapping')
            ->addOption('crawl', null, InputOption::VALUE_NONE, 'Crawl internal links recursively')
            ->addOption('depth', null, InputOption::VALUE_REQUIRED, 'Maximum crawling depth', '3')
            ->addOption('max-pages', null, InputOption::VALUE_REQUIRED, 'Maximum number of pages to crawl', '20')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Accessibility scan level (1-5)', '4');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $target = $input->getArgument('target');
        if (!is_string($target)) {
            $io->error("Target must be a string URL or file path.");
            return Command::FAILURE;
        }
        $enableAi = (bool)$input->getOption('ai');
        $projectPathOption = $input->getOption('project-path');
        $projectPath = is_string($projectPathOption) ? $projectPathOption : getcwd();
        if ($projectPath === false) {
            $projectPath = '.';
        }

        $levelOption = $input->getOption('level');
        $level = is_numeric($levelOption) ? (int)$levelOption : 4;
        if ($level < 1 || $level > 5) {
            $io->error("Scan level must be between 1 and 5.");
            return Command::FAILURE;
        }

        $io->title("Accessibility Console - Scanning: $target (Level $level)");

        // 1. Setup Scanner
        $scanner = new Scanner();
        $rules = RuleLevels::getRulesForLevel($level);
        foreach ($rules as $rule) {
            $scanner->addRule($rule);
        }

        // 2. Fetch and Scan
        $isUrl = str_starts_with($target, 'http://') || str_starts_with($target, 'https://');
        $crawl = (bool)$input->getOption('crawl') && $isUrl;

        /** @var array<string, \YakNet\AccessibilityConsole\Core\Violation[]> $results */
        $results = [];

        if ($crawl) {
            $depthOption = $input->getOption('depth');
            $depthLimit = is_numeric($depthOption) ? (int)$depthOption : 3;
            $maxPagesOption = $input->getOption('max-pages');
            $maxPages = is_numeric($maxPagesOption) ? (int)$maxPagesOption : 20;
            
            $io->comment("Starting recursive crawl from: $target (Max Depth: $depthLimit, Max Pages: $maxPages)");

            $parsedBase = parse_url($target);
            $baseHost = $parsedBase['host'] ?? '';

            $queue = [
                ['url' => $target, 'depth' => 1]
            ];
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
                if (!is_array($current)) {
                    continue;
                }
                
                $url = $current['url'];
                $depth = (int)$current['depth'];

                // Remove fragment
                $cleanUrl = strtok($url, '#');
                if ($cleanUrl === false || isset($visited[$cleanUrl])) {
                    continue;
                }

                $visited[$cleanUrl] = true;
                $io->write("  <fg=gray>Crawling [Depth $depth]:</> <fg=cyan>$cleanUrl</>... ");

                try {
                    $response = $client->request('GET', $cleanUrl);
                    $html = (string)$response->getBody();
                    $io->writeln("<fg=green>OK</>");
                    
                    // Scan the page HTML
                    $pageViolations = $scanner->scan($html);
                    $results[$cleanUrl] = $pageViolations;

                    // Extract links if depth is less than limit
                    if ($depth < $depthLimit) {
                        $html5 = new \Masterminds\HTML5(['disable_html_ns' => true]);
                        $doc = $html5->loadHTML($html);
                        $links = $doc->getElementsByTagName('a');
                        
                        foreach ($links as $link) {
                            $href = $link->getAttribute('href');
                            if (empty($href) || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                                continue;
                            }

                            // Resolve relative URI
                            try {
                                $baseUri = new \GuzzleHttp\Psr7\Uri($cleanUrl);
                                $relativeUri = new \GuzzleHttp\Psr7\Uri($href);
                                $absoluteUri = \GuzzleHttp\Psr7\UriResolver::resolve($baseUri, $relativeUri);
                                $absoluteUrl = (string)$absoluteUri;
                            } catch (\Throwable) {
                                continue;
                            }

                            // Check host compatibility
                            $parsedAbsolute = parse_url($absoluteUrl);
                            $absoluteHost = $parsedAbsolute['host'] ?? '';
                            
                            if ($absoluteHost === $baseHost) {
                                // Add to queue if not already visited or in queue
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
            // Standard single page/file scan
            $html = @file_get_contents($target);
            if ($html === false || $html === '') {
                $io->error("Could not read target: $target");
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

        // 3. Setup Helpers
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
                
                // Show Snippet
                $io->writeln("  <fg=gray>Snippet:</> <fg=yellow>" . htmlspecialchars($violation->htmlSnippet) . "</>");

                // Source Mapping
                $location = $locator->locate($violation->htmlSnippet);
                if ($location) {
                    $io->writeln("  <fg=gray>Source:</>  <fg=cyan>{$location['file']}:{$location['line']}</>");
                }

                // AI Fixer
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

        $io->warning("Scanned " . $totalViolations . " errors. Use --ai to get fix suggestions.");

        // 4. Export Report
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

        return Command::SUCCESS;
    }
}
