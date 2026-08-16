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

    public function getEngine(): RuleEngine
    {
        return $this->engine;
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

        $html5 = new \Masterminds\HTML5([
            'disable_html_ns' => true,
        ]);
        $doc = $html5->loadHTML($html);

        // Attach raw HTML property for syntax/token level analysis
        /** @phpstan-ignore-next-line */
        $doc->rawHtml = $html;

        return $this->engine->run($doc);
    }
}
