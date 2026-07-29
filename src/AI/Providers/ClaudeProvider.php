<?php

namespace YakNet\AccessibilityConsole\AI\Providers;

use GuzzleHttp\Client as GuzzleClient;
use YakNet\AccessibilityConsole\AI\AiProviderInterface;
use YakNet\AccessibilityConsole\AI\PromptBuilder;
use YakNet\AccessibilityConsole\Core\Violation;

class ClaudeProvider implements AiProviderInterface
{
    private ?GuzzleClient $client = null;

    public function __construct(private readonly ?string $apiKey)
    {
        if ($this->apiKey) {
            $this->client = new GuzzleClient([
                'base_uri' => 'https://api.anthropic.com/v1/',
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 10.0,
            ]);
        }
    }

    public function getName(): string
    {
        return 'Anthropic Claude';
    }

    public function suggestFix(Violation $violation): ?string
    {
        if (!$this->client) {
            return null;
        }

        $prompt = PromptBuilder::build($violation);

        try {
            $response = $this->client->post('messages', [
                'json' => [
                    'model' => 'claude-3-5-haiku-20241022',
                    'max_tokens' => 1000,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (is_array($data)
                && isset($data['content']) && is_array($data['content'])
                && isset($data['content'][0]) && is_array($data['content'][0])
                && isset($data['content'][0]['text']) && is_string($data['content'][0]['text'])
            ) {
                return $data['content'][0]['text'];
            }
            return null;
        } catch (\Throwable $e) {
            return "Claude Error: " . $e->getMessage();
        }
    }
}
