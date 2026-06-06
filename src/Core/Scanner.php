<?php

namespace YakNet\AccessibilityConsole\Core;

class Scanner
{
    private RuleEngine $engine;

    public function __construct(?RuleEngine $engine = null)
    {
        $this->engine = $engine ?? new RuleEngine();
    }

    public function addRule(mixed $rule): void
    {
        $this->engine->addRule($rule);
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
