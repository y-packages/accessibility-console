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
    }

    public static function fromYaml(string $path): self
    {
        // Placeholder for YAML loading logic
        return new self([]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function getGeminiApiKey(): ?string
    {
        return $this->get('gemini_api_key') ?? $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: null;
    }
}
