<?php

namespace YakNet\AccessibilityConsole\Reporting;

use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\Violation;

class JunitFormatter implements FormatterInterface
{
    /**
     * @param Violation[] $violations
     * @param int $baselinedCount
     * @param SymfonyStyle $io
     * @return int
     */
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $xml = $this->render($violations, $baselinedCount);
        $io->write($xml);

        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Render the violations in JUnit XML format.
     *
     * @param Violation[] $violations
     * @param int $baselinedCount
     * @return string
     */
    public function render(array $violations, int $baselinedCount = 0): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $testsuites = $dom->createElement('testsuites');
        $testsuites->setAttribute('name', 'YakNet Accessibility Console');
        $testsuites->setAttribute('tests', (string)max(1, count($violations)));
        $testsuites->setAttribute('failures', (string)count($violations));
        $testsuites->setAttribute('errors', '0');
        $testsuites->setAttribute('time', '0.000');
        $dom->appendChild($testsuites);

        if (empty($violations)) {
            $testsuite = $dom->createElement('testsuite');
            $testsuite->setAttribute('name', 'AccessibilityScan');
            $testsuite->setAttribute('tests', '1');
            $testsuite->setAttribute('failures', '0');
            $testsuite->setAttribute('errors', '0');
            $testsuite->setAttribute('time', '0.000');

            $testcase = $dom->createElement('testcase');
            $testcase->setAttribute('name', 'AccessibilityCompliant');
            $testcase->setAttribute('classname', 'YakNet.AccessibilityConsole');
            $testcase->setAttribute('time', '0.000');

            $testsuite->appendChild($testcase);
            $testsuites->appendChild($testsuite);

            return (string)$dom->saveXML();
        }

        // Group violations by file
        /** @var array<string, Violation[]> $grouped */
        $grouped = [];
        foreach ($violations as $v) {
            $file = $v->location['file'] ?? 'unknown';
            $grouped[$file][] = $v;
        }

        foreach ($grouped as $file => $fileViolations) {
            $testsuite = $dom->createElement('testsuite');
            $testsuite->setAttribute('name', str_replace('\\', '/', $file));
            $testsuite->setAttribute('tests', (string)count($fileViolations));
            $testsuite->setAttribute('failures', (string)count($fileViolations));
            $testsuite->setAttribute('errors', '0');
            $testsuite->setAttribute('time', '0.000');

            foreach ($fileViolations as $v) {
                $line = $v->location['line'] ?? 1;
                $col = $v->location['column'] ?? 1;

                $testcase = $dom->createElement('testcase');
                $testcase->setAttribute('name', sprintf('[%s] %s', $v->ruleId, $v->message));
                $testcase->setAttribute('classname', str_replace(['/', '\\'], '.', $file));
                $testcase->setAttribute('time', '0.000');

                $failure = $dom->createElement('failure');
                $failure->setAttribute('type', $v->severity->value);
                $failure->setAttribute('message', $v->message);

                $details = sprintf(
                    "Rule: %s (%s)\nStandard: %s\nSeverity: %s\nLocation: %s:%d:%d\n\nSnippet:\n%s",
                    $v->ruleId,
                    $v->message,
                    $v->standard->value,
                    $v->severity->value,
                    $file,
                    $line,
                    $col,
                    $v->htmlSnippet
                );

                if ($v->fixSuggestion !== null && $v->fixSuggestion !== '') {
                    $details .= "\n\nSuggested Fix:\n" . $v->fixSuggestion;
                }

                $cdata = $dom->createCDATASection($details);
                $failure->appendChild($cdata);

                $testcase->appendChild($failure);
                $testsuite->appendChild($testcase);
            }

            $testsuites->appendChild($testsuite);
        }

        return (string)$dom->saveXML();
    }
}
