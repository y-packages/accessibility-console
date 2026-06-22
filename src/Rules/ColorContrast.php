<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ColorContrast extends AbstractRule
{
    /** @var array<string, array<string, array{color?: string, background-color?: string}>> */
    private array $documentStylesCache = [];

    public function getId(): string { return 'WCAG_1_4_3_CONTRAST'; }
    public function getDescription(): string { return 'Text must have sufficient contrast against the background (4.5:1 minimum).'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 5; }

    public function check(DOMElement $element): ?Violation
    {
        // 1. Parse inline styles (highest priority)
        $inlineStyle = $element->hasAttribute('style') ? $element->getAttribute('style') : '';
        $color = $this->parseColorValue($inlineStyle, 'color');
        $bg = $this->parseColorValue($inlineStyle, 'background-color') ?: $this->parseColorValue($inlineStyle, 'background');

        // 2. Fallback: Parse stylesheet styles
        if (($color === null || $bg === null) && $element->ownerDocument !== null) {
            $docStyles = $this->parseDocumentStyles($element->ownerDocument);
            
            // Loop through all parsed styles. Later matching rules override earlier ones
            foreach ($docStyles as $selector => $styles) {
                if ($this->elementMatchesSelector($element, $selector)) {
                    if ($color === null && isset($styles['color'])) {
                        $color = $styles['color'];
                    }
                    if ($bg === null && isset($styles['background-color'])) {
                        $bg = $styles['background-color'];
                    }
                }
            }
        }

        if ($color === null || $bg === null) {
            return null;
        }

        $cRGB = $this->hexToRgb($color);
        $bgRGB = $this->hexToRgb($bg);

        if ($cRGB === null || $bgRGB === null) {
            return null;
        }

        $cLum = $this->getLuminance($cRGB[0], $cRGB[1], $cRGB[2]);
        $bgLum = $this->getLuminance($bgRGB[0], $bgRGB[1], $bgRGB[2]);

        $ratio = ($cLum > $bgLum) ? ($cLum + 0.05) / ($bgLum + 0.05) : ($bgLum + 0.05) / ($cLum + 0.05);

        // Check font size & weight for large text rules
        $isLarge = false;
        $fontSizeStyle = $this->parseFontSize($inlineStyle, $element);
        if ($fontSizeStyle !== null) {
            $isLarge = $this->isLargeText($fontSizeStyle, $this->parseFontWeight($inlineStyle, $element));
        }

        $minRatio = $isLarge ? 3.0 : 4.5;

        if ($ratio < $minRatio) {
            $formattedRatio = round($ratio, 2);
            return $this->createViolation(
                $element,
                "Insufficient contrast ratio of {$formattedRatio}:1. Minimum required is {$minRatio}:1.",
                "Adjust colors to increase contrast. For example, text: {$color}, background: {$bg}."
            );
        }

        return null;
    }

    private function parseFontSize(string $style, DOMElement $element): ?string
    {
        if (preg_match('/font-size\s*:\s*([^;]+)/i', $style, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function parseFontWeight(string $style, DOMElement $element): ?string
    {
        if (preg_match('/font-weight\s*:\s*([^;]+)/i', $style, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function isLargeText(string $size, ?string $weight): bool
    {
        if (preg_match('/([0-9.]+)(px|em|rem|pt)/i', $size, $matches)) {
            $val = (float)$matches[1];
            $unit = strtolower($matches[2]);
            $isBold = $weight !== null && preg_match('/^(bold|[7-9]00)$/i', $weight);

            if ($isBold) {
                return ($unit === 'px' && $val >= 18) || ($unit === 'pt' && $val >= 14) || (($unit === 'em' || $unit === 'rem') && $val >= 1.2);
            } else {
                return ($unit === 'px' && $val >= 24) || ($unit === 'pt' && $val >= 18) || (($unit === 'em' || $unit === 'rem') && $val >= 1.5);
            }
        }
        return false;
    }

    /**
     * @return array<string, array{color?: string, background-color?: string}>
     */
    private function parseDocumentStyles(\DOMDocument $doc): array
    {
        $hash = spl_object_hash($doc);
        if (isset($this->documentStylesCache[$hash])) {
            return $this->documentStylesCache[$hash];
        }

        $mergedStyles = [];
        $styleTags = $doc->getElementsByTagName('style');
        
        foreach ($styleTags as $tag) {
            $cssText = $tag->textContent;
            if (trim($cssText) === '') {
                continue;
            }

            try {
                $parser = new \Sabberworm\CSS\Parser($cssText);
                $cssDoc = $parser->parse();
                
                foreach ($cssDoc->getAllDeclarationBlocks() as $block) {
                    $rules = [];
                    foreach ($block->getDeclarations() as $decl) {
                        $prop = strtolower($decl->getPropertyName());
                        if ($prop === 'color' || $prop === 'background-color' || $prop === 'background') {
                            // Extract string value
                            $valObj = $decl->getValue();
                            $val = is_object($valObj) && method_exists($valObj, '__toString') 
                                ? (string)$valObj 
                                : (is_string($valObj) ? $valObj : '');

                            if ($prop === 'color') {
                                $normalized = $this->normalizeColorValue($val);
                                if ($normalized !== null) {
                                    $rules['color'] = $normalized;
                                }
                            } elseif ($prop === 'background-color') {
                                $normalized = $this->normalizeColorValue($val);
                                if ($normalized !== null) {
                                    $rules['background-color'] = $normalized;
                                }
                            } elseif ($prop === 'background') {
                                $normalized = $this->extractColorFromBackground($val);
                                if ($normalized !== null) {
                                    $rules['background-color'] = $normalized;
                                }
                            }
                        }
                    }

                    if (empty($rules)) {
                        continue;
                    }

                    foreach ($block->getSelectors() as $selector) {
                        $selectorString = trim($selector->getSelector());
                        if ($selectorString !== '') {
                            if (!isset($mergedStyles[$selectorString])) {
                                $mergedStyles[$selectorString] = [];
                            }
                            $mergedStyles[$selectorString] = array_merge($mergedStyles[$selectorString], $rules);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore CSS parser failures
            }
        }

        $this->documentStylesCache[$hash] = $mergedStyles;
        return $mergedStyles;
    }

    private function elementMatchesSelector(DOMElement $element, string $selector): bool
    {
        $parts = array_map('trim', explode(',', $selector));
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (str_starts_with($part, '#')) {
                $id = substr($part, 1);
                if ($element->getAttribute('id') === $id) {
                    return true;
                }
                continue;
            }

            if (str_starts_with($part, '.')) {
                $class = substr($part, 1);
                $classes = array_filter(array_map('trim', explode(' ', $element->getAttribute('class'))));
                if (in_array($class, $classes, true)) {
                    return true;
                }
                continue;
            }

            if (strtolower($element->tagName) === strtolower($part)) {
                return true;
            }

            if (preg_match('/^([a-zA-Z0-9_-]+)\.([a-zA-Z0-9_-]+)$/', $part, $matches)) {
                $tag = $matches[1];
                $class = $matches[2];
                $classes = array_filter(array_map('trim', explode(' ', $element->getAttribute('class'))));
                if (strtolower($element->tagName) === strtolower($tag) && in_array($class, $classes, true)) {
                    return true;
                }
            }
            
            if (preg_match('/^([a-zA-Z0-9_-]+)#([a-zA-Z0-9_-]+)$/', $part, $matches)) {
                $tag = $matches[1];
                $id = $matches[2];
                if (strtolower($element->tagName) === strtolower($tag) && $element->getAttribute('id') === $id) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalizeColorValue(string $val): ?string
    {
        $val = trim($val);
        if (preg_match('/^#([0-9a-fA-F]{3,6})$/', $val)) {
            return $val;
        }

        if (preg_match('/rgba?\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)/i', $val, $matches)) {
            return sprintf("#%02x%02x%02x", (int)$matches[1], (int)$matches[2], (int)$matches[3]);
        }

        $colors = [
            'black' => '#000000', 'white' => '#ffffff', 'red' => '#ff0000',
            'green' => '#008000', 'blue' => '#0000ff', 'yellow' => '#ffff00',
            'gray' => '#808080', 'grey' => '#808080', 'silver' => '#c0c0c0',
        ];
        if (isset($colors[strtolower($val)])) {
            return $colors[strtolower($val)];
        }

        return null;
    }

    private function extractColorFromBackground(string $val): ?string
    {
        $parts = explode(' ', $val);
        foreach ($parts as $part) {
            $color = $this->normalizeColorValue($part);
            if ($color !== null) {
                return $color;
            }
        }
        return null;
    }

    private function parseColorValue(string $style, string $property): ?string
    {
        if (preg_match('/(?:^|;)\s*' . preg_quote($property, '/') . '\s*:\s*([^;]+)/i', $style, $matches)) {
            return $this->normalizeColorValue(trim($matches[1]));
        }
        return null;
    }

    /**
     * @return array{0: int, 1: int, 2: int}|null
     */
    private function hexToRgb(string $hex): ?array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $r = (int)hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = (int)hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = (int)hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            return [$r, $g, $b];
        } elseif (strlen($hex) === 6) {
            $r = (int)hexdec(substr($hex, 0, 2));
            $g = (int)hexdec(substr($hex, 2, 2));
            $b = (int)hexdec(substr($hex, 4, 2));
            return [$r, $g, $b];
        }
        return null;
    }

    private function getLuminance(float $r, float $g, float $b): float
    {
        $rs = $r / 255.0;
        $gs = $g / 255.0;
        $bs = $b / 255.0;

        $rVal = ($rs <= 0.03928) ? $rs / 12.92 : pow(($rs + 0.055) / 1.055, 2.4);
        $gVal = ($gs <= 0.03928) ? $gs / 12.92 : pow(($gs + 0.055) / 1.055, 2.4);
        $bVal = ($bs <= 0.03928) ? $bs / 12.92 : pow(($bs + 0.055) / 1.055, 2.4);

        return 0.2126 * $rVal + 0.7152 * $gVal + 0.0722 * $bVal;
    }
}
