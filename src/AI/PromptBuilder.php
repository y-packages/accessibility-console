<?php

namespace YakNet\AccessibilityConsole\AI;

use YakNet\AccessibilityConsole\Core\Violation;

class PromptBuilder
{
    public static function build(Violation $violation): string
    {
        return <<<PROMPT
You are a WCAG 2.1 Accessibility expert. 
A static analysis tool found an accessibility issue in this HTML snippet.

Snippet: {$violation->htmlSnippet}
Error Message: {$violation->message}
Rule ID: {$violation->ruleId}
Severity: {$violation->severity->value}
Standard: {$violation->standard->value}

Please provide a short explanation of the fix in Turkish and the fixed version of the HTML snippet.
Use the following EXACT format:
EXPLANATION: [A short explanation of what you fixed and why, in Turkish]
FIX: [Only the corrected HTML snippet, no surrounding explanation]
PROMPT;
    }
}
