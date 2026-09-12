<?php

namespace YakNet\AccessibilityConsole\Tests\Notifications;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Notifications\NotificationDispatcher;
use YakNet\AccessibilityConsole\Notifications\NotificationPayload;
use YakNet\AccessibilityConsole\Notifications\NotifierInterface;

class NotificationDispatcherTest extends TestCase
{
    public function testDispatchToMultipleNotifiers(): void
    {
        $dispatcher = new NotificationDispatcher();

        $mockSlack = $this->createMock(NotifierInterface::class);
        $mockSlack->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $mockDiscord = $this->createMock(NotifierInterface::class);
        $mockDiscord->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $dispatcher->addNotifier('slack', $mockSlack);
        $dispatcher->addNotifier('discord', $mockDiscord);

        $this->assertTrue($dispatcher->hasNotifiers());

        $payload = new NotificationPayload('https://example.com', 95, 2, 0, 2);
        $results = $dispatcher->dispatch($payload);

        $this->assertTrue($results['slack']);
        $this->assertTrue($results['discord']);
    }
}
