<?php

namespace YakNet\AccessibilityConsole\AI;

use YakNet\AccessibilityConsole\AI\Providers\GeminiProvider;
use YakNet\AccessibilityConsole\AI\Providers\OpenAiProvider;
use YakNet\AccessibilityConsole\AI\Providers\ClaudeProvider;
use YakNet\AccessibilityConsole\AI\Providers\MockAiProvider;
use YakNet\AccessibilityConsole\Core\Violation;

class AiFixerManager
{
    /** @var array<string, AiProviderInterface> */
    private array $providers = [];
    private ?string $activeProviderName = null;

    public function __construct(?string $geminiApiKey = null, ?string $openAiApiKey = null, ?string $claudeApiKey = null)
    {
        // Register default providers
        if ($geminiApiKey) {
            $this->registerProvider('gemini', new GeminiProvider($geminiApiKey));
            $this->activeProviderName = 'gemini';
        }
        if ($openAiApiKey) {
            $this->registerProvider('openai', new OpenAiProvider($openAiApiKey));
            if (!$this->activeProviderName) {
                $this->activeProviderName = 'openai';
            }
        }
        if ($claudeApiKey) {
            $this->registerProvider('claude', new ClaudeProvider($claudeApiKey));
            if (!$this->activeProviderName) {
                $this->activeProviderName = 'claude';
            }
        }

        // Always register mock provider as fallback/testing option
        $this->registerProvider('mock', new MockAiProvider());
        if (!$this->activeProviderName) {
            $this->activeProviderName = 'mock';
        }
    }

    public function registerProvider(string $name, AiProviderInterface $provider): void
    {
        $this->providers[strtolower($name)] = $provider;
    }

    public function setActiveProvider(string $name): void
    {
        $key = strtolower($name);
        if (isset($this->providers[$key])) {
            $this->activeProviderName = $key;
        }
    }

    public function getActiveProvider(): ?AiProviderInterface
    {
        if ($this->activeProviderName && isset($this->providers[$this->activeProviderName])) {
            return $this->providers[$this->activeProviderName];
        }
        return null;
    }

    public function suggestFix(Violation $violation): ?string
    {
        $provider = $this->getActiveProvider();
        if ($provider) {
            return $provider->suggestFix($violation);
        }
        return "No active AI provider configured.";
    }
}
