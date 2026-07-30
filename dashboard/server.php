<?php

namespace YakNet\AccessibilityConsole\Dashboard;

class Server
{
    /**
     * Start the built-in development dashboard server.
     *
     * @param string $host Host address (default: 127.0.0.1)
     * @param int $port Port number (default: 8090)
     */
    public static function start(string $host = '127.0.0.1', int $port = 8090): void
    {
        $publicDir = __DIR__ . '/public';
        $cmd = sprintf('php -S %s:%d -t %s', $host, $port, escapeshellarg($publicDir));

        echo "🚀 YakNet Accessibility Console Web Dashboard running at http://{$host}:{$port}\n";
        echo "Press Ctrl+C to stop the dashboard server.\n\n";

        passthru($cmd);
    }
}
