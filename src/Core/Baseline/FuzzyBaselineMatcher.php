<?php

namespace YakNet\AccessibilityConsole\Core\Baseline;

class FuzzyBaselineMatcher
{
    /**
     * Determine if two HTML snippets are fuzzy matched.
     *
     * @param string $snippetA
     * @param string $snippetB
     * @return bool
     */
    public static function match(string $snippetA, string $snippetB): bool
    {
        $normA = self::normalize($snippetA);
        $normB = self::normalize($snippetB);

        if ($normA === '' || $normB === '') {
            return false;
        }

        return $normA === $normB;
    }

    /**
     * Normalize HTML snippet by stripping formatting, converting to lowercase,
     * and sorting attributes.
     *
     * @param string $html
     * @return string
     */
    public static function normalize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        // Parse using simple regex to sort attributes and lowercase tag
        if (preg_match('/^<([a-zA-Z0-9:-]+)(.*?)>$/s', $html, $matches)) {
            $tag = strtolower($matches[1]);
            $attrsContent = $matches[2];

            // Parse attributes
            preg_match_all('/([a-zA-Z0-9:-]+)=["\'](.*?)["\']/s', $attrsContent, $attrMatches, PREG_SET_ORDER);
            
            $attrs = [];
            foreach ($attrMatches as $match) {
                $attrs[strtolower($match[1])] = trim($match[2]);
            }
            ksort($attrs);

            $attrStr = '';
            foreach ($attrs as $name => $value) {
                $attrStr .= sprintf(' %s="%s"', $name, $value);
            }

            return sprintf('<%s%s>', $tag, $attrStr);
        }

        // Fallback: Strip all whitespace and lowercase
        $cleaned = preg_replace('/\s+/', '', $html) ?: '';
        return strtolower($cleaned);
    }
}
