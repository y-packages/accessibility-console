<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\CI;

/**
 * Generates ready-to-use CI/CD pipeline configuration files for GitHub Actions,
 * GitLab CI, Azure Pipelines, and Bitbucket Pipelines with YakNet Accessibility Console.
 */
class CiWorkflowGenerator
{
    /**
     * List supported CI/CD platform identifiers.
     *
     * @return string[]
     */
    public static function getSupportedPlatforms(): array
    {
        return ['github', 'gitlab', 'azure', 'bitbucket'];
    }

    /**
     * Get the conventional repository file path for a given CI platform.
     */
    public static function getDefaultPath(string $platform): string
    {
        return match (strtolower($platform)) {
            'github' => '.github/workflows/accessibility.yml',
            'gitlab' => '.gitlab-ci.a11y.yml',
            'azure' => 'azure-pipelines.a11y.yml',
            'bitbucket' => 'bitbucket-pipelines.a11y.yml',
            default => throw new \InvalidArgumentException("Unsupported CI platform '{$platform}'."),
        };
    }

    /**
     * Generate CI workflow YAML content for the specified platform.
     */
    public function generate(
        string $platform,
        string $preset = 'wcag22-aa',
        int $minScore = 90,
        string $targetPath = 'resources/views'
    ): string {
        return match (strtolower($platform)) {
            'github' => $this->generateGitHubActions($preset, $minScore, $targetPath),
            'gitlab' => $this->generateGitLabCi($preset, $minScore, $targetPath),
            'azure' => $this->generateAzurePipelines($preset, $minScore, $targetPath),
            'bitbucket' => $this->generateBitbucketPipelines($preset, $minScore, $targetPath),
            default => throw new \InvalidArgumentException(sprintf(
                "Unsupported CI platform '%s'. Supported platforms: %s.",
                $platform,
                implode(', ', self::getSupportedPlatforms())
            )),
        };
    }

    private function generateGitHubActions(string $preset, int $minScore, string $targetPath): string
    {
        return <<<YAML
name: Accessibility Audit (YakNet)

on:
  push:
    branches: [ main, master ]
  pull_request:

permissions:
  contents: read
  security-events: write

jobs:
  accessibility:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: dom, libxml, mbstring

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Run YakNet Accessibility Console
        run: |
          vendor/bin/a11y scan {$targetPath} \
            --preset={$preset} \
            --min-score={$minScore} \
            --report=a11y.sarif \
            --vpat=vpat-2.5.md \
            --badge=a11y-badge.svg

      - name: Upload SARIF to GitHub Code Scanning
        if: always()
        uses: github/codeql-action/upload-sarif@v3
        with:
          sarif_file: a11y.sarif

      - name: Upload VPAT 2.5 & Badge Artifacts
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: accessibility-reports
          path: |
            vpat-2.5.md
            a11y-badge.svg
YAML;
    }

    private function generateGitLabCi(string $preset, int $minScore, string $targetPath): string
    {
        return <<<YAML
accessibility_audit:
  stage: test
  image: php:8.3-cli
  before_script:
    - composer install --no-interaction --prefer-dist
  script:
    - vendor/bin/a11y scan {$targetPath} --preset={$preset} --min-score={$minScore} --format=gitlab > gl-a11y-codequality.json
    - vendor/bin/a11y vpat {$targetPath} --preset={$preset} --format=html --output=vpat-2.5.html
  artifacts:
    when: always
    reports:
      codequality: gl-a11y-codequality.json
    paths:
      - vpat-2.5.html
YAML;
    }

    private function generateAzurePipelines(string $preset, int $minScore, string $targetPath): string
    {
        return <<<YAML
trigger:
  - main

pool:
  vmImage: ubuntu-latest

steps:
  - script: composer install --no-interaction --prefer-dist
    displayName: 'Install Composer Dependencies'

  - script: |
      vendor/bin/a11y scan {$targetPath} \
        --preset={$preset} \
        --min-score={$minScore} \
        --report=a11y-junit.xml \
        --vpat=vpat-2.5.md
    displayName: 'Run YakNet Accessibility Audit'

  - task: PublishTestResults@2
    condition: always()
    inputs:
      testResultsFormat: 'JUnit'
      testResultsFiles: 'a11y-junit.xml'
      testRunTitle: 'YakNet Accessibility Audit'
YAML;
    }

    private function generateBitbucketPipelines(string $preset, int $minScore, string $targetPath): string
    {
        return <<<YAML
image: php:8.3-cli

pipelines:
  default:
    - step:
        name: YakNet Accessibility Audit
        caches:
          - composer
        script:
          - composer install --no-interaction --prefer-dist
          - vendor/bin/a11y scan {$targetPath} --preset={$preset} --min-score={$minScore} --report=a11y-junit.xml --vpat=vpat-2.5.md
        artifacts:
          - a11y-junit.xml
          - vpat-2.5.md
YAML;
    }
}
