<?php

namespace YakNet\AccessibilityConsole\Syntax;

class HtmlSyntaxLinter
{
    /** @var array<int, string> */
    private static array $voidElements = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 
        'link', 'meta', 'param', 'source', 'track', 'wbr'
    ];

    /**
     * Lint raw HTML/template content for markup syntax errors and structural defects.
     *
     * @param string $html
     * @return SyntaxIssue[]
     */
    public function lint(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $issues = [];

        // 1. Check for unclosed comments
        $this->checkUnclosedComments($html, $issues);

        // 2. Check for duplicate attributes on same tag & malformed attribute syntax
        $this->checkAttributes($html, $issues);

        // 3. Check for invalid self-closing on non-void elements
        $this->checkInvalidSelfClosing($html, $issues);

        // 4. Check for unclosed / mismatched tags (structural validation)
        $this->checkTagStructure($html, $issues);

        // 5. Check multiple DOCTYPE / HEAD / BODY declarations
        $this->checkStructureTagMultiplicity($html, $issues);

        return $issues;
    }

    /**
     * @param SyntaxIssue[] $issues
     */
    private function checkUnclosedComments(string $html, array &$issues): void
    {
        $offset = 0;
        while (($pos = strpos($html, '<!--', $offset)) !== false) {
            $endPos = strpos($html, '-->', $pos + 4);
            if ($endPos === false) {
                $line = $this->getLineFromOffset($html, $pos);
                $issues[] = new SyntaxIssue(
                    code: 'SYNTAX_UNCLOSED_COMMENT',
                    message: 'HTML comment is opened with <!-- but never closed with -->.',
                    line: $line,
                    snippet: mb_substr($html, $pos, 80),
                    fixSuggestion: 'Close the comment with -->.',
                    severity: 'error'
                );
                break;
            }
            $offset = $endPos + 3;
        }
    }

    /**
     * @param SyntaxIssue[] $issues
     */
    private function checkAttributes(string $html, array &$issues): void
    {
        // Match start tags <tagname attributes...>
        $pattern = '/<([a-zA-Z0-9:-]+)(\s+[^>]*)?>/s';
        if (preg_match_all($pattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $fullTag = $match[0];
                $offset = $match[1];
                $tagName = strtolower($matches[1][$index][0]);

                if (str_starts_with($fullTag, '<?') || str_starts_with($fullTag, '<!')) {
                    continue;
                }

                $attrString = $matches[2][$index][0] ?? '';
                if (trim($attrString) === '') {
                    continue;
                }

                $line = $this->getLineFromOffset($html, $offset);

                // A. Check for missing space between attributes (e.g. href="x"class="y")
                if (preg_match('/["\']([a-zA-Z0-9_:-]+)=["\']/', $attrString, $missingSpaceMatch, PREG_OFFSET_CAPTURE)) {
                    $mOffset = $offset + (int)$missingSpaceMatch[0][1];
                    $mLine = $this->getLineFromOffset($html, $mOffset);
                    $issues[] = new SyntaxIssue(
                        code: 'SYNTAX_MISSING_ATTR_SPACE',
                        message: "Missing whitespace separator between attributes on <{$tagName}>.",
                        line: $mLine,
                        snippet: $fullTag,
                        fixSuggestion: 'Insert a space between adjacent attributes.',
                        severity: 'warning'
                    );
                }

                // B. Parse individual attributes to check for duplicates
                $attrNames = [];
                $attrPattern = '/([a-zA-Z0-9_:-]+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+)))?/s';
                if (preg_match_all($attrPattern, $attrString, $attrMatches)) {
                    foreach ($attrMatches[1] as $aName) {
                        $normalizedName = strtolower(trim($aName));
                        if ($normalizedName === '' || $normalizedName === '/') {
                            continue;
                        }

                        if (isset($attrNames[$normalizedName])) {
                            $issues[] = new SyntaxIssue(
                                code: 'SYNTAX_DUPLICATE_ATTR',
                                message: "Duplicate attribute '{$normalizedName}' declared multiple times on <{$tagName}> element (HTML5 3.2.4 violation).",
                                line: $line,
                                snippet: $fullTag,
                                fixSuggestion: "Remove the duplicate '{$normalizedName}' attribute.",
                                severity: 'error'
                            );
                        } else {
                            $attrNames[$normalizedName] = true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param SyntaxIssue[] $issues
     */
    private function checkInvalidSelfClosing(string $html, array &$issues): void
    {
        // Match <tagname ... /> where tagname is NOT in void elements list and not SVG/MathML
        $pattern = '/<([a-zA-Z0-9-]+)(\s+[^>]*)?\/>/s';
        if (preg_match_all($pattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $fullTag = $match[0];
                $offset = $match[1];
                $tagName = strtolower($matches[1][$index][0]);

                if (in_array($tagName, self::$voidElements, true)) {
                    continue; // Allowed to self-close in HTML5/XHTML
                }

                // Skip SVG and MathML subtrees/elements
                if (in_array($tagName, ['path', 'circle', 'rect', 'line', 'polygon', 'polyline', 'ellipse', 'use', 'stop'], true)) {
                    continue;
                }

                $line = $this->getLineFromOffset($html, $offset);
                $issues[] = new SyntaxIssue(
                    code: 'SYNTAX_INVALID_SELF_CLOSING',
                    message: "Self-closing syntax (<{$tagName} />) is invalid on non-void HTML element <{$tagName}>. It causes DOM tree distortion.",
                    line: $line,
                    snippet: $fullTag,
                    fixSuggestion: "Replace with explicit opening and closing tags: <{$tagName}></{$tagName}>.",
                    severity: 'error'
                );
            }
        }
    }

    /**
     * @param SyntaxIssue[] $issues
     */
    private function checkTagStructure(string $html, array &$issues): void
    {
        // Strip comments and script/style contents for structure check
        $clean = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;
        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $clean) ?? $clean;
        $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $clean) ?? $clean;
        $clean = preg_replace('/<\?php.*?\?>/si', '', $clean) ?? $clean;

        // Find all tags in order: <tag> or </tag>
        $tagPattern = '/<(\/)?([a-zA-Z0-9:-]+)(?:\s+[^>]*)?>/s';
        if (preg_match_all($tagPattern, $clean, $matches, PREG_OFFSET_CAPTURE)) {
            $stack = [];
            $trackedTags = [
                'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'ul', 'ol', 'li', 'dl', 'dt', 'dd',
                'details', 'summary', 'dialog', 'main', 'nav', 'header', 'footer', 
                'section', 'article', 'aside', 'form', 'fieldset', 'div', 'span', 'p', 'a', 'button'
            ];

            foreach ($matches[0] as $index => $match) {
                $fullMatch = $match[0];
                $offset = $match[1];
                $isClosing = ($matches[1][$index][0] === '/');
                $tagName = strtolower($matches[2][$index][0]);

                if (in_array($tagName, self::$voidElements, true)) {
                    continue;
                }

                if (str_starts_with($fullMatch, '<?') || str_starts_with($fullMatch, '<!')) {
                    continue;
                }

                // Check self-closing syntax
                if (str_ends_with(trim($fullMatch), '/>')) {
                    continue;
                }

                if (!in_array($tagName, $trackedTags, true)) {
                    continue;
                }

                if (!$isClosing) {
                    $stack[] = ['tag' => $tagName, 'offset' => $offset, 'line' => $this->getLineFromOffset($clean, $offset)];
                } else {
                    if (empty($stack)) {
                        $line = $this->getLineFromOffset($clean, $offset);
                        $issues[] = new SyntaxIssue(
                            code: 'SYNTAX_UNOPENED_TAG',
                            message: "Closing tag </{$tagName}> has no matching opening tag.",
                            line: $line,
                            snippet: $fullMatch,
                            fixSuggestion: "Remove the stray </{$tagName}> closing tag.",
                            severity: 'error'
                        );
                    } else {
                        $last = end($stack);
                        if ($last['tag'] === $tagName) {
                            array_pop($stack);
                        } else {
                            // Find if $tagName exists deeper in the stack
                            $foundIndex = null;
                            for ($i = count($stack) - 1; $i >= 0; $i--) {
                                if ($stack[$i]['tag'] === $tagName) {
                                    $foundIndex = $i;
                                    break;
                                }
                            }

                            if ($foundIndex !== null) {
                                $unclosed = array_slice($stack, $foundIndex + 1);
                                $stack = array_slice($stack, 0, $foundIndex);
                                $line = $this->getLineFromOffset($clean, $offset);
                                foreach ($unclosed as $u) {
                                    $issues[] = new SyntaxIssue(
                                        code: 'SYNTAX_MISMATCHED_TAG',
                                        message: "Mismatched closing tag </{$tagName}>; <{$u['tag']}> opened at line {$u['line']} was not closed properly.",
                                        line: $line,
                                        snippet: $fullMatch,
                                        fixSuggestion: "Close <{$u['tag']}> before </{$tagName}>.",
                                        severity: 'error'
                                    );
                                }
                            } else {
                                $line = $this->getLineFromOffset($clean, $offset);
                                $issues[] = new SyntaxIssue(
                                    code: 'SYNTAX_UNOPENED_TAG',
                                    message: "Closing tag </{$tagName}> has no matching opening tag.",
                                    line: $line,
                                    snippet: $fullMatch,
                                    fixSuggestion: "Remove the stray </{$tagName}> closing tag.",
                                    severity: 'error'
                                );
                            }
                        }
                    }
                }
            }

            // Check any unclosed structural tags left on stack
            foreach ($stack as $unclosed) {
                // Ignore optional closing tags like p or li if unclosed at document end
                if (in_array($unclosed['tag'], ['p', 'li', 'dt', 'dd', 'span'], true)) {
                    continue;
                }
                $issues[] = new SyntaxIssue(
                    code: 'SYNTAX_UNCLOSED_TAG',
                    message: "Unclosed tag <{$unclosed['tag']}> opened at line {$unclosed['line']} was never closed.",
                    line: $unclosed['line'],
                    snippet: "<{$unclosed['tag']}>",
                    fixSuggestion: "Add matching closing tag </{$unclosed['tag']}>.",
                    severity: 'error'
                );
            }
        }
    }

    /**
     * @param SyntaxIssue[] $issues
     */
    private function checkStructureTagMultiplicity(string $html, array &$issues): void
    {
        // 1. Multiple <body> tags
        if (preg_match_all('/<body\b[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            if (count($matches[0]) > 1) {
                foreach (array_slice($matches[0], 1) as $match) {
                    $line = $this->getLineFromOffset($html, $match[1]);
                    $issues[] = new SyntaxIssue(
                        code: 'SYNTAX_MULTIPLE_BODY',
                        message: 'Document contains multiple <body> elements. Only one <body> is permitted per HTML document.',
                        line: $line,
                        snippet: $match[0],
                        fixSuggestion: 'Merge contents into a single <body> element.',
                        severity: 'error'
                    );
                }
            }
        }

        // 2. Multiple <head> tags
        if (preg_match_all('/<head\b[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            if (count($matches[0]) > 1) {
                foreach (array_slice($matches[0], 1) as $match) {
                    $line = $this->getLineFromOffset($html, $match[1]);
                    $issues[] = new SyntaxIssue(
                        code: 'SYNTAX_MULTIPLE_HEAD',
                        message: 'Document contains multiple <head> elements. Only one <head> is permitted per HTML document.',
                        line: $line,
                        snippet: $match[0],
                        fixSuggestion: 'Merge head tags into a single <head> element.',
                        severity: 'error'
                    );
                }
            }
        }
    }

    private function getLineFromOffset(string $content, int $offset): int
    {
        if ($offset <= 0) {
            return 1;
        }
        $sub = substr($content, 0, $offset);
        return substr_count($sub, "\n") + 1;
    }
}
