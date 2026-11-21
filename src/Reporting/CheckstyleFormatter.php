<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Reporting;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;

/**
 * Formats accessibility violations in standard Checkstyle XML format
 * for integration with Jenkins, PhpStorm, SonarQube, and GitLab CI.
 */
class CheckstyleFormatter implements FormatterInterface
{
    /**
     * @param Violation[] $violations
     * @param int $baselinedCount
     * @param SymfonyStyle $io
     * @return int
     */
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $io->write($this->render($violations));
        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Render Checkstyle XML string.
     *
     * @param Violation[] $violations
     */
    public function render(array $violations): string
    {
        /** @var array<string, Violation[]> $grouped */
        $grouped = [];
        foreach ($violations as $v) {
            $file = $v->location['file'] ?? 'unknown';
            $grouped[$file][] = $v;
        }

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<checkstyle version="4.0.0">';

        foreach ($grouped as $file => $fileViolations) {
            $xml[] = sprintf('  <file name="%s">', $this->escapeAttr($file));
            foreach ($fileViolations as $v) {
                $line = isset($v->location['line']) ? max(1, (int)$v->location['line']) : 1;
                $col = isset($v->location['column']) ? max(1, (int)$v->location['column']) : 1;
                $sev = match ($v->severity) {
                    Severity::CRITICAL, Severity::ERROR => 'error',
                    Severity::WARNING => 'warning',
                    Severity::INFO => 'info',
                };
                $source = 'YakNet.Accessibility.' . $v->ruleId;

                $xml[] = sprintf(
                    '    <error line="%d" column="%d" severity="%s" message="%s" source="%s"/>',
                    $line,
                    $col,
                    $sev,
                    $this->escapeAttr($v->message),
                    $this->escapeAttr($source)
                );
            }
            $xml[] = '  </file>';
        }

        $xml[] = '</checkstyle>';

        return implode("\n", $xml) . "\n";
    }

    private function escapeAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
