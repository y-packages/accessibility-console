<?php

namespace YakNet\AccessibilityConsole\Core;

enum Severity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';

    public function color(): string
    {
        return match($this) {
            self::INFO => 'info',
            self::WARNING => 'comment',
            self::ERROR => 'error',
            self::CRITICAL => 'error',
        };
    }
}
