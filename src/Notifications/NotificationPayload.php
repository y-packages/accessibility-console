<?php

namespace YakNet\AccessibilityConsole\Notifications;

class NotificationPayload
{
    /**
     * @param array<int, array<string, mixed>> $topViolations
     */
    public function __construct(
        public string $target,
        public int $healthScore,
        public int $totalViolations,
        public int $errorCount,
        public int $warningCount,
        public array $topViolations = []
    ) {}
}
