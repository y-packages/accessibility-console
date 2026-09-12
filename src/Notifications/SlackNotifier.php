<?php

namespace YakNet\AccessibilityConsole\Notifications;

use GuzzleHttp\Client;

class SlackNotifier implements NotifierInterface
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

        $color = $payload->healthScore >= 90 ? '#10b981' : ($payload->healthScore >= 70 ? '#f59e0b' : '#ef4444');
        $status = $payload->healthScore >= 90 ? 'PASSED' : 'FAILED';

        $body = [
            'attachments' => [
                [
                    'color' => $color,
                    'title' => "♿ Accessibility Audit: {$payload->target}",
                    'text' => sprintf(
                        "*Health Score:* %d/100 (%s)\n*Total Violations:* %d (Errors: %d, Warnings: %d)",
                        $payload->healthScore,
                        $status,
                        $payload->totalViolations,
                        $payload->errorCount,
                        $payload->warningCount
                    ),
                    'footer' => 'YakNet Accessibility Console',
                    'ts' => time(),
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
