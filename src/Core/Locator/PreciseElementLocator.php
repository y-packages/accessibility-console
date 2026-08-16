<?php

namespace YakNet\AccessibilityConsole\Core\Locator;

class PreciseElementLocator
{
    /**
     * Locate the exact line and column of an HTML snippet within file content.
     *
     * @param string $content Full file content
     * @param string $snippet HTML snippet of the violating element
     * @return array{line: int, column: int}
     */
    public function locate(string $content, string $snippet): array
    {
        $cleanSnippet = trim(preg_replace('/\s+/', ' ', $snippet) ?? '');
        if ($cleanSnippet === '') {
            return ['line' => 1, 'column' => 1];
        }

        // 1. Exact substring match pass
        $exactPos = strpos($content, $snippet);
        if ($exactPos !== false) {
            return $this->getLineAndColumn($content, $exactPos);
        }

        // 2. Normalized whitespace search pass
        $lines = explode("\n", $content);
        $searchSnippet = mb_strlen($cleanSnippet) > 120 ? mb_substr($cleanSnippet, 0, 120) : $cleanSnippet;

        foreach ($lines as $index => $line) {
            $cleanLine = trim(preg_replace('/\s+/', ' ', $line) ?? '');
            if ($cleanLine !== '' && str_contains($cleanLine, $searchSnippet)) {
                $col = strpos($line, mb_substr($searchSnippet, 0, 20));
                return [
                    'line' => $index + 1,
                    'column' => ($col !== false) ? $col + 1 : 1
                ];
            }
        }

        // 3. Tag & Attribute Token Match Pass
        preg_match('/<([a-zA-Z0-9:-]+)/', $snippet, $tagMatch);
        $tagName = strtolower($tagMatch[1] ?? '');

        if ($tagName !== '') {
            preg_match_all('/([a-zA-Z0-9_:-]+)=["\']([^"\']*)["\']/', $snippet, $attrMatches, PREG_SET_ORDER);
            $attributes = [];
            foreach ($attrMatches as $m) {
                $attributes[$m[1]] = $m[2];
            }

            $bestLine = 1;
            $bestCol = 1;
            $maxScore = 0.0;

            foreach ($lines as $index => $line) {
                $lineLower = strtolower($line);
                $tagStr = '<' . $tagName;

                if (!str_contains($lineLower, $tagStr)) {
                    continue;
                }

                $score = 1.0;

                foreach ($attributes as $attrName => $attrVal) {
                    if (str_contains($line, "$attrName=\"$attrVal\"") || str_contains($line, "$attrName='$attrVal'")) {
                        $score += 3.0;
                    } elseif (str_contains($line, "$attrName=")) {
                        $score += 1.0;
                    }
                }

                if ($score > $maxScore) {
                    $maxScore = $score;
                    $bestLine = $index + 1;
                    $col = strpos($lineLower, $tagStr);
                    $bestCol = ($col !== false) ? $col + 1 : 1;
                }
            }

            if ($maxScore > 0) {
                return ['line' => $bestLine, 'column' => $bestCol];
            }
        }

        return ['line' => 1, 'column' => 1];
    }

    /**
     * @return array{line: int, column: int}
     */
    private function getLineAndColumn(string $content, int $offset): array
    {
        if ($offset <= 0) {
            return ['line' => 1, 'column' => 1];
        }

        $before = substr($content, 0, $offset);
        $line = substr_count($before, "\n") + 1;
        $lastNewline = strrpos($before, "\n");
        $column = ($lastNewline === false) ? $offset + 1 : $offset - $lastNewline;

        return ['line' => $line, 'column' => $column];
    }
}
