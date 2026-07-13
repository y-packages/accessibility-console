<?php

namespace YakNet\AccessibilityConsole\Parser;

interface ParserInterface
{
    /**
     * Parse template file contents and strip out dynamic content placeholders
     * to return clean, standard HTML for scanning.
     *
     * @param string $content
     * @return string
     */
    public function parse(string $content): string;
}
