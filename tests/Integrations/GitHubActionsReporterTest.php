<?php

namespace YakNet\AccessibilityConsole\Tests\Integrations;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Integrations\GitHubActionsReporter;

class GitHubActionsReporterTest extends TestCase
{
    public function testFormatsGitHubAnnotations(): void
    {
        $scanResult = [
            'violations' => [
                [
                    'ruleId' => 'WCAG_1_1_1_SVG',
                    'description' => 'SVG missing alt',
                    'line' => 12,
                    'severity' => 'ERROR'
                ]
            ]
        ];

        $output = GitHubActionsReporter::formatAnnotations($scanResult, 'index.html');
        $this->assertStringContainsString('::error file=index.html,line=12::[WCAG_1_1_1_SVG]', $output);
    }
}
