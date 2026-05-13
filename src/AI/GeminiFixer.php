<?php

namespace YakNet\AccessibilityConsole\AI;

use Gemini;
use YakNet\AccessibilityConsole\Core\Violation;

class GeminiFixer
{
    private ?\Gemini\Client $client = null;

    public function __construct(private readonly ?string $apiKey)
    {
        if ($this->apiKey && class_exists(Gemini::class)) {
            $this->client = Gemini::client($this->apiKey);
        }
    }

    public function suggestFix(Violation $violation): ?string
    {
        if (!$this->client) {
            return null;
        }

        $prompt = <<<PROMPT
You are a WCAG 2.1 Accessibility expert. 
A scanner found an error in this HTML snippet:
Snippet: {$violation->htmlSnippet}
Error: {$violation->message}
Severity: {$violation->severity->value}
Standard: {$violation->standard->value}

Please provide ONLY the fixed version of the HTML snippet. 
Do not explain, do not add markdown code blocks, just return the corrected HTML.
If the error is about missing 'alt' text, add a meaningful alt text based on context.
If it's about ARIA roles, add the correct ones.
PROMPT;

        try {
            $result = $this->client->generativeModel('gemini-2.5-flash')->generateContent($prompt);
            $suggestion = trim($result->text());
            
            // Clean up if AI included markdown blocks anyway
            $suggestion = preg_replace('/^```html\s*|\s*```$/i', '', $suggestion);
            
            return $suggestion;
        } catch (\Throwable $e) {
            return "AI Düzeltme Hatası: " . $e->getMessage();
        }
    }
}
