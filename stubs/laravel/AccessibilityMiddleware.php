<?php

namespace YakNet\AccessibilityConsole\Stubs\Laravel;

use Closure;
use YakNet\AccessibilityConsole\Core\Scanner;

class AccessibilityMiddleware
{
    /**
     * Handle an incoming request in Laravel.
     * Injects a real-time WCAG Accessibility badge into HTML responses when app is in debug mode.
     */
    public function handle(mixed $request, Closure $next): mixed
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if (!method_exists($response, 'getContent') || !method_exists($response, 'setContent')) {
            return $response;
        }

        $content = strval($response->getContent());
        if (!str_contains($content, '</html>')) {
            return $response;
        }

        $scanner = new Scanner();
        $result = $scanner->scan($content);
        $score = intval($result['score'] ?? 0);
        $badgeColor = $score >= 80 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444');

        $badgeHtml = "
        <div id='yaknet-a11y-badge' style='position:fixed; bottom:16px; right:16px; z-index:999999; font-family:sans-serif; background:#0f172a; color:#fff; padding:8px 16px; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.3); font-size:12px; display:flex; align-items:center; border:1px solid rgba(255,255,255,0.1); cursor:pointer;' onclick='alert(\"YakNet Accessibility Console Score: {$score}/100\")'>
            <span style='font-size:16px; margin-right:6px;'>♿</span>
            <span>A11y Score: <strong style='color:{$badgeColor};'>{$score}/100</strong></span>
        </div>";

        $response->setContent(str_replace('</body>', $badgeHtml . '</body>', $content));
        return $response;
    }
}
