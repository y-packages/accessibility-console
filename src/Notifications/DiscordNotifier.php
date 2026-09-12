<?php

namespace YakNet\AccessibilityConsole\Notifications;

use GuzzleHttp\Client;

class DiscordNotifier implements NotifierInterface
{
    private Client $httpClient;

    public function __construct(
        private string $webhookUrl,
        ?Client $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new Client(['timeout' => 5.0]);
    }

    public function send(NotificationPayload $payload): bool
    {
        if ($this->webhookUrl === '') {
            return false;
        }

        // Color in decimal for Discord embed
        $color = $payload->healthScore >= 90 ? 0x10b981 : ($payload->healthScore >= 70 ? 0xf59e0b : 0xef4444);

        $body = [
            'embeds' => [
                [
                    'title' => "♿ Accessibility Audit: {$payload->target}",
                    'color' => $color,
                    'fields' => [
                        [
                            'name' => 'Health Score',
                            'value' => sprintf('%d / 100', $payload->healthScore),
                            'inline' => true,
                        ],
                        [
                            'name' => 'Total Violations',
                            'value' => (string)$payload->totalViolations,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Breakdown',
                            'value' => sprintf('Errors: %d | Warnings: %d', $payload->errorCount, $payload->warningCount),
                            'inline' => false,
                        ],
                    ],
                    'footer' => [
                        'text' => 'YakNet Accessibility Console',
                    ],
                    'timestamp' => date('c'),
                ],
            ],
        ];

        try {
            $response = $this->httpClient->post($this->webhookUrl, [
                'json' => $body,
            ]);
            return $response->getStatusCode() < 300;
        } catch (\Throwable) {
            return false;
        }
    }
}
