<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Notifications;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Notifications\GenericWebhookNotifier;
use YakNet\AccessibilityConsole\Notifications\MicrosoftTeamsNotifier;
use YakNet\AccessibilityConsole\Notifications\NotificationPayload;

class ExpandedNotifiersTest extends TestCase
{
    public function testMicrosoftTeamsNotifierPostsMessageCard(): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([new Response(200, [], '1')]);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new Client(['handler' => $stack]);

        $notifier = new MicrosoftTeamsNotifier('https://teams.example.test/webhook', $client);
        $payload = new NotificationPayload('views/', 94, 2, 0, 2);

        $this->assertTrue($notifier->send($payload));
        $this->assertCount(1, $container);
        $body = (string)$container[0]['request']->getBody();
        $this->assertStringContainsString('MessageCard', $body);
        $this->assertStringContainsString('YakNet Accessibility Console', $body);
    }

    public function testGenericWebhookNotifierSignsPayloadWithHmacSha256(): void
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([new Response(202, [], '{"ok":true}')]);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new Client(['handler' => $stack]);

        $notifier = new GenericWebhookNotifier('https://hooks.example.test/a11y', 'super-secret-key', $client);
        $payload = new NotificationPayload('templates/', 88, 3, 1, 2);

        $this->assertTrue($notifier->send($payload));
        $this->assertCount(1, $container);
        $sigHeader = $container[0]['request']->getHeaderLine('X-YakNet-Signature-256');
        $this->assertStringStartsWith('sha256=', $sigHeader);
    }
}
