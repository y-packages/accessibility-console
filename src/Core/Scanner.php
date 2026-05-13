<?php

namespace YakNet\AccessibilityConsole\Core;

use DOMDocument;
use YakNet\AccessibilityConsole\Rules\RuleInterface;

class Scanner
{
    private array $rules = [];

    public function __construct(
        private readonly ?RuleEngine $engine = null
    ) {
        if ($this->engine) {
            $this->rules = $this->engine->getRules();
        }
    }

    public function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * @return Violation[]
     */
    public function scan(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $dom = new DOMDocument();
        $previousState = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        if (!$loaded) {
            return [];
        }

        $violations = [];
        $elements = $dom->getElementsByTagName('*');

        foreach ($elements as $element) {
            if (!$element instanceof \DOMElement) continue;
            foreach ($this->rules as $rule) {
                try {
                    $violation = $rule->check($element);
                    if ($violation) {
                        $violations[] = $violation;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return $violations;
    }
}
