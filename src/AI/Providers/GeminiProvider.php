<?php

namespace YakNet\AccessibilityConsole\AI\Providers;

use Gemini;
use YakNet\AccessibilityConsole\AI\AiProviderInterface;
use YakNet\AccessibilityConsole\AI\PromptBuilder;
use YakNet\AccessibilityConsole\Core\Violation;

class GeminiProvider implements AiProviderInterface
{
    private ?\Gemini\Client $client = null;

    public function __construct(private readonly ?string $apiKey)
    {
        if ($this->apiKey) {
            try {
                $this->client = \Gemini::client($this->apiKey);
            } catch (\Throwable) {
                $this->client = null;
            }
        }
    }

    public function getName(): string
    {
        return 'Google Gemini';
    }

    public function suggestFix(Violation $violation): ?string
    {
        if (!$this->apiKey || !$this->client) {
            return null;
        }

        $prompt = PromptBuilder::build($violation);

        $models = ['gemini-2.5-pro', 'gemini-2.5-flash', 'gemini-1.5-pro', 'gemini-1.5-flash'];
        $lastError = null;

        foreach ($models as $model) {
            try {
                $result = $this->client->generativeModel($model)->generateContent($prompt);
                return trim($result->text());
            } catch (\Throwable $e) {
                $lastError = $e;
            }
        }

        return "Gemini Error: " . $lastError->getMessage();
    }
}
