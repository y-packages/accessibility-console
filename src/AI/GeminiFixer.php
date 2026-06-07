<?php

namespace YakNet\AccessibilityConsole\AI;

use Gemini;
use YakNet\AccessibilityConsole\Core\Violation;

class GeminiFixer
{
    private ?\Gemini\Client $client = null;

    public function __construct(private readonly ?string $apiKey)
    {
        if ($this->apiKey) {
            try {
                $this->client = \Gemini::client($this->apiKey);
            } catch (\Throwable) {
                $this->client = null;
            }
        }
    }

    public function suggestFix(Violation $violation): ?string
    {
        if (!$this->apiKey) {
            return "Hata: GEMINI_API_KEY bulunamadı.";
        }

        if (!$this->client) {
            return "Hata: Gemini istemcisi başlatılamadı. Kütüphane eksik olabilir.";
        }

        $prompt = <<<PROMPT
You are a WCAG 2.1 Accessibility expert. 
A scanner found an error in this HTML snippet:
Snippet: {$violation->htmlSnippet}
Error: {$violation->message}
Severity: {$violation->severity->value}
Standard: {$violation->standard->value}

Please provide a short explanation of the fix (in Turkish) and the fixed version of the HTML snippet.
Use the following EXACT format:
EXPLANATION: [A short explanation of what you fixed and why]
FIX: [Only the corrected HTML snippet]
PROMPT;

        try {
            $result = $this->client->generativeModel('gemini-3.1-flash-lite')->generateContent($prompt);
            return trim($result->text());
        } catch (\Throwable $e) {
            return "AI Düzeltme Hatası: " . $e->getMessage();
        }
    }
}
