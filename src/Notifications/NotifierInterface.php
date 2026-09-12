<?php

namespace YakNet\AccessibilityConsole\Notifications;

interface NotifierInterface
{
    public function send(NotificationPayload $payload): bool;
}
