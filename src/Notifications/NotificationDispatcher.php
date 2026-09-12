<?php

namespace YakNet\AccessibilityConsole\Notifications;

class NotificationDispatcher
{
    /**
     * @var array<string, NotifierInterface>
     */
    private array $notifiers = [];

    public function addNotifier(string $channel, NotifierInterface $notifier): void
    {
        $this->notifiers[$channel] = $notifier;
    }

    /**
     * Dispatch notification payload across all registered notifiers.
     *
     * @return array<string, bool> Channel delivery results
     */
    public function dispatch(NotificationPayload $payload): array
    {
        $results = [];
        foreach ($this->notifiers as $channel => $notifier) {
            $results[$channel] = $notifier->send($payload);
        }
        return $results;
    }

    public function hasNotifiers(): bool
    {
        return !empty($this->notifiers);
    }
}
