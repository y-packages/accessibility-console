<?php

namespace YakNet\AccessibilityConsole\Core;

class Config
{
    /** @var array<string, mixed> */
    private array $settings = [];

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->loadDotenv();
    }

    private function loadDotenv(): void
    {
        $cwd = getcwd();
        if ($cwd && file_exists($cwd . '/.env')) {
            try {
                $dotenv = \Dotenv\Dotenv::createImmutable($cwd);
                $dotenv->safeLoad();
            } catch (\Throwable $e) {
                // Ignore Dotenv errors silently
            }
        }
    }

    public static function fromYaml(string $path): self
    {
        if (file_exists($path)) {
            try {
                $content = \Symfony\Component\Yaml\Yaml::parseFile($path);
                if (is_array($content)) {
                    /** @var array<string, mixed> $content */
                    return new self($content);
                }
            } catch (\Throwable $e) {
                // Ignore parsing errors
            }
        }
        return new self([]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * @return array<int, string>
     */
    public function getPaths(): array
    {
        $paths = $this->get('paths');
        if (is_array($paths)) {
            return array_values(array_filter($paths, 'is_string'));
        }
        if (is_string($paths)) {
            return [$paths];
        }
        return ['.']; // default to current directory
    }

    /**
     * @return array<int, string>
     */
    public function getExcludePaths(): array
    {
        $exclude = $this->get('exclude_paths');
        if (is_array($exclude)) {
            return array_values(array_filter($exclude, 'is_string'));
        }
        if (is_string($exclude)) {
            return [$exclude];
        }
        return ['vendor', 'node_modules', 'tests', 'storage', '.git']; // sensible defaults
    }

    public function getLevel(): int
    {
        $level = $this->get('level', 4);
        return is_numeric($level) ? (int)$level : 4;
    }

    public function getBaselinePath(): ?string
    {
        $baseline = $this->get('baseline');
        return is_string($baseline) ? $baseline : null;
    }

    public function getFormat(): string
    {
        $format = $this->get('format', 'console');
        return is_string($format) ? $format : 'console';
    }

    /**
     * @return array{include: array<int, string>, exclude: array<int, string>}
     */
    public function getRulesConfig(): array
    {
        $rules = $this->get('rules', []);
        $include = [];
        $exclude = [];

        if (is_array($rules)) {
            if (isset($rules['include']) && is_array($rules['include'])) {
                $include = array_values(array_filter($rules['include'], 'is_string'));
            }
            if (isset($rules['exclude']) && is_array($rules['exclude'])) {
                $exclude = array_values(array_filter($rules['exclude'], 'is_string'));
            }
        }

        return [
            'include' => $include,
            'exclude' => $exclude
        ];
    }

    public function getGeminiApiKey(): ?string
    {
        $key = $this->get('gemini_api_key') ?? $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: null;
        return is_string($key) ? $key : null;
    }

    public function getPreset(): ?string
    {
        $preset = $this->get('preset');
        return is_string($preset) && $preset !== '' ? $preset : null;
    }

    /**
     * Governance policy configuration block from a11y.yaml.
     *
     * @return array<string, mixed>
     */
    public function getPolicyConfig(): array
    {
        $policy = $this->get('policy', []);
        if (!is_array($policy)) {
            return [];
        }
        /** @var array<string, mixed> $policy */
        return $policy;
    }

    /**
     * Notification webhook configuration block from a11y.yaml.
     *
     * @return array<string, mixed>
     */
    public function getNotificationsConfig(): array
    {
        $notifications = $this->get('notifications', []);
        if (!is_array($notifications)) {
            return [];
        }
        /** @var array<string, mixed> $notifications */
        return $notifications;
    }

    /**
     * File extensions to include during directory scans.
     *
     * @return array<int, string>
     */
    public function getExtensions(): array
    {
        $extensions = $this->get('extensions');
        if (is_array($extensions)) {
            return array_values(array_filter($extensions, 'is_string'));
        }
        return ['php', 'html', 'htm', 'xhtml', 'twig', 'blade.php', 'vue', 'jsx', 'tsx', 'latte'];
    }
}
