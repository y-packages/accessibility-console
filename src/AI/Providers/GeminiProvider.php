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

        try {
            $result = $this->client->generativeModel('gemini-3.1-flash-lite')->generateContent($prompt);
            return trim($result->text());
        } catch (\Throwable $e) {
            return "Gemini Error: " . $e->getMessage();
        }
    }
}
