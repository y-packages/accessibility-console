<?php

namespace YakNet\AccessibilityConsole\Parser;

class TemplatePreprocessor
{
    private BladeParser $bladeParser;
    private TwigParser $twigParser;
    private HtmlParser $htmlParser;

    public function __construct(
        ?BladeParser $bladeParser = null,
        ?TwigParser $twigParser = null,
        ?HtmlParser $htmlParser = null
    ) {
        $this->bladeParser = $bladeParser ?? new BladeParser();
        $this->twigParser = $twigParser ?? new TwigParser();
        $this->htmlParser = $htmlParser ?? new HtmlParser();
    }

    /**
     * Preprocess file content according to its file extension or template dialect.
     *
     * @param string $content
     * @param string|null $filePath
     * @return string
     */
    public function preprocess(string $content, ?string $filePath = null): string
    {
        if ($filePath !== null) {
            $lower = strtolower($filePath);
            if (str_ends_with($lower, '.blade.php')) {
                return $this->bladeParser->parse($content);
            }
            if (str_ends_with($lower, '.twig')) {
                return $this->twigParser->parse($content);
            }
            if (str_ends_with($lower, '.php')) {
                return $this->preprocessPhp($content);
            }
        }

        // Detect Blade or Twig by content heuristic if no path is provided
        if (str_contains($content, '{{') || str_contains($content, '@if') || str_contains($content, '@foreach')) {
            return $this->bladeParser->parse($content);
        }

        if (str_contains($content, '{%') || str_contains($content, '{#')) {
            return $this->twigParser->parse($content);
        }

        if (str_contains($content, '<' . '?php') || str_contains($content, '<' . '?=')) {
            return $this->preprocessPhp($content);
        }

        return $this->htmlParser->parse($content);
    }

    private function preprocessPhp(string $content): string
    {
        // Replace short echo tags with placeholder text preserving newlines
        $content = preg_replace_callback('/<\?=(.*?)\?>/s', function ($m) {
            $newlines = str_repeat("\n", substr_count($m[0], "\n"));
            return 'StaticPhpEcho' . $newlines;
        }, $content) ?? $content;

        // Replace PHP execution blocks with placeholder preserving newlines
        $content = preg_replace_callback('/<\?php(.*?)\?>/s', function ($m) {
            return str_repeat("\n", substr_count($m[0], "\n"));
        }, $content) ?? $content;

        return $content;
    }
}
