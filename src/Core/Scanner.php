<?php

namespace YakNet\AccessibilityConsole\Core;

class Scanner
{
    public function __construct(private ?RuleEngine $engine = null)
    {
        if ($this->engine === null) {
            $this->engine = new RuleEngine();
        }
    }

    /**
     * Scan HTML content for accessibility violations.
     *
     * @param string $html
     * @return Violation[]
     */
    public function scan(string $html): array
    {
        if (empty(trim($html))) {
            return [];
        }

        $doc = new \DOMDocument();
        // Suppress warnings for malformed HTML
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $this->engine->run($doc);
    }
}
