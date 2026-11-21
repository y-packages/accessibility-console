<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Notifications;

use GuzzleHttp\Client;

/**
 * Sends accessibility scan summaries to Microsoft Teams channels via Incoming Webhook.
 */
class MicrosoftTeamsNotifier implements NotifierInterface
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

        $themeColor = $payload->healthScore >= 90 ? '10B981' : ($payload->healthScore >= 70 ? 'F59E0B' : 'EF4444');
        $status = $payload->healthScore >= 90 ? 'PASSED' : 'NEEDS ATTENTION';

        $body = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'themeColor' => $themeColor,
            'summary' => "Accessibility Audit: {$payload->target} ({$payload->healthScore}/100)",
            'sections' => [
                [
                    'activityTitle' => "♿ YakNet Accessibility Console — {$payload->target}",
                    'activitySubtitle' => "Status: {$status}",
                    'facts' => [
                        ['name' => 'Health Score', 'value' => "{$payload->healthScore}/100"],
                        ['name' => 'Total Violations', 'value' => (string)$payload->totalViolations],
                        ['name' => 'Errors / Critical', 'value' => (string)$payload->errorCount],
                        ['name' => 'Warnings', 'value' => (string)$payload->warningCount],
                    ],
                    'markdown' => true,
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
