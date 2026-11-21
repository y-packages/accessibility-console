<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Notifications;

use GuzzleHttp\Client;

/**
 * Dispatches structured JSON accessibility payloads to any custom webhook endpoint
 * (SIEM, Jira Automation, ServiceNow, internal dashboards) with optional HMAC-SHA256 signing.
 */
class GenericWebhookNotifier implements NotifierInterface
{
    private Client $httpClient;

    public function __construct(
        private string $webhookUrl,
        private ?string $signingSecret = null,
        ?Client $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new Client(['timeout' => 5.0]);
    }

    public function send(NotificationPayload $payload): bool
    {
        if ($this->webhookUrl === '') {
            return false;
        }

        $data = [
            'tool' => 'YakNet Accessibility Console',
            'version' => '4.0.0',
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'target' => $payload->target,
            'health_score' => $payload->healthScore,
            'total_violations' => $payload->totalViolations,
            'error_count' => $payload->errorCount,
            'warning_count' => $payload->warningCount,
            'top_violations' => $payload->topViolations,
        ];

        $jsonBody = (string)json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'YakNet-Accessibility-Console/4.0',
        ];

        if ($this->signingSecret !== null && $this->signingSecret !== '') {
            $headers['X-YakNet-Signature-256'] = 'sha256=' . hash_hmac('sha256', $jsonBody, $this->signingSecret);
        }

        try {
            $response = $this->httpClient->post($this->webhookUrl, [
                'headers' => $headers,
                'body' => $jsonBody,
            ]);
            return $response->getStatusCode() < 300;
        } catch (\Throwable) {
            return false;
        }
    }
}
