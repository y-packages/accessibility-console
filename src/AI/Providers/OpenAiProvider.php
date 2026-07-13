<?php

namespace YakNet\AccessibilityConsole\AI\Providers;

use GuzzleHttp\Client as GuzzleClient;
use YakNet\AccessibilityConsole\AI\AiProviderInterface;
use YakNet\AccessibilityConsole\AI\PromptBuilder;
use YakNet\AccessibilityConsole\Core\Violation;

class OpenAiProvider implements AiProviderInterface
{
    private ?GuzzleClient $client = null;

    public function __construct(private readonly ?string $apiKey)
    {
        if ($this->apiKey) {
            $this->client = new GuzzleClient([
                'base_uri' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 10.0,
            ]);
        }
    }

    public function getName(): string
    {
        return 'OpenAI GPT';
    }

    public function suggestFix(Violation $violation): ?string
    {
        if (!$this->client) {
            return null;
        }

        $prompt = PromptBuilder::build($violation);

        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.2,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (is_array($data) && isset($data['choices'][0]['message']['content']) && is_string($data['choices'][0]['message']['content'])) {
                return $data['choices'][0]['message']['content'];
            }
            return null;
        } catch (\Throwable $e) {
            return "OpenAI Error: " . $e->getMessage();
        }
    }
}
