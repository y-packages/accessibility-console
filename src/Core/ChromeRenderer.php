<?php

namespace YakNet\AccessibilityConsole\Core;

use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Navigation;

class ChromeRenderer
{
    /**
     * Navigate to URL using headless Chrome and return fully rendered HTML.
     */
    public function render(string $url): string
    {
        $browserFactory = new BrowserFactory();

        $options = [
            'windowSize' => [1280, 1024],
            'noSandbox'  => true,
        ];

        // Windows Chrome binary path auto-detection override
        $chromeBinary = $_ENV['CHROME_BINARY'] ?? getenv('CHROME_BINARY') ?: null;
        if (!$chromeBinary && PHP_OS_FAMILY === 'Windows') {
            $standardPaths = [
                'C:/Program Files/Google/Chrome/Application/chrome.exe',
                'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
                'C:/Program Files/Google/Chrome/Application/chrome.exe'
            ];
            foreach ($standardPaths as $path) {
                if (file_exists($path)) {
                    $chromeBinary = $path;
                    break;
                }
            }
        }

        if ($chromeBinary) {
            $options['chromeBinary'] = $chromeBinary;
        }

        $browser = $browserFactory->createBrowser($options);

        try {
            $page = $browser->createPage();
            
            // Navigate and wait for onload event
            $navigation = $page->navigate($url);
            $navigation->waitForNavigation(\HeadlessChromium\Page::LOAD, 10000); // 10 seconds timeout

            // Extra wait for async JS rendering to complete
            usleep(1500000); // 1.5s

            $html = $page->getHtml();
        } finally {
            $browser->close();
        }

        return $html;
    }
}
