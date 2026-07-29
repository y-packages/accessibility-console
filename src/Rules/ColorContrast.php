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
            'aliceblue' => '#f0f8ff', 'antiquewhite' => '#faebd7', 'aqua' => '#00ffff',
            'aquamarine' => '#7fffd4', 'azure' => '#f0ffff', 'beige' => '#f5f5dc',
            'bisque' => '#ffe4c4', 'black' => '#000000', 'blanchedalmond' => '#ffebcd',
            'blue' => '#0000ff', 'blueviolet' => '#8a2be2', 'brown' => '#a52a2a',
            'burlywood' => '#deb887', 'cadetblue' => '#5f9ea0', 'chartreuse' => '#7fff00',
            'chocolate' => '#d2691e', 'coral' => '#ff7f50', 'cornflowerblue' => '#6495ed',
            'cornsilk' => '#fff8dc', 'crimson' => '#dc143c', 'cyan' => '#00ffff',
            'darkblue' => '#00008b', 'darkcyan' => '#008b8b', 'darkgoldenrod' => '#b8860b',
            'darkgray' => '#a9a9a9', 'darkgreen' => '#006400', 'darkgrey' => '#a9a9a9',
            'darkkhaki' => '#bdb76b', 'darkmagenta' => '#8b008b', 'darkolivegreen' => '#556b2f',
            'darkorange' => '#ff8c00', 'darkorchid' => '#9932cc', 'darkred' => '#8b0000',
            'darksalmon' => '#e9967a', 'darkseagreen' => '#8fbc8f', 'darkslateblue' => '#483d8b',
            'darkslategray' => '#2f4f4f', 'darkslategrey' => '#2f4f4f', 'darkturquoise' => '#00ced1',
            'darkviolet' => '#9400d3', 'deeppink' => '#ff1493', 'deepskyblue' => '#00bfff',
            'dimgray' => '#696969', 'dimgrey' => '#696969', 'dodgerblue' => '#1e90ff',
            'firebrick' => '#b22222', 'floralwhite' => '#fffaf0', 'forestgreen' => '#228b22',
            'fuchsia' => '#ff00ff', 'gainsboro' => '#dcdcdc', 'ghostwhite' => '#f8f8ff',
            'gold' => '#ffd700', 'goldenrod' => '#daa520', 'gray' => '#808080',
            'green' => '#008000', 'greenyellow' => '#adff2f', 'grey' => '#808080',
            'honeydew' => '#f0fff0', 'hotpink' => '#ff69b4', 'indianred' => '#cd5c5c',
            'indigo' => '#4b0082', 'ivory' => '#fffff0', 'khaki' => '#f0e68c',
            'lavender' => '#e6e6fa', 'lavenderblush' => '#fff0f5', 'lawngreen' => '#7cfc00',
            'lemonchiffon' => '#fffacd', 'lightblue' => '#add8e6', 'lightcoral' => '#f08080',
            'lightcyan' => '#e0ffff', 'lightgoldenrodyellow' => '#fafad2', 'lightgray' => '#d3d3d3',
            'lightgreen' => '#90ee90', 'lightgrey' => '#d3d3d3', 'lightpink' => '#ffb6c1',
            'lightsalmon' => '#ffa07a', 'lightseagreen' => '#20b2aa', 'lightskyblue' => '#87cefa',
            'lightslategray' => '#778899', 'lightslategrey' => '#778899', 'lightsteelblue' => '#b0c4de',
            'lightyellow' => '#ffffe0', 'lime' => '#00ff00', 'limegreen' => '#32cd32',
            'linen' => '#faf0e6', 'magenta' => '#ff00ff', 'maroon' => '#800000',
            'mediumaquamarine' => '#66cdaa', 'mediumblue' => '#0000cd', 'mediumorchid' => '#ba55d3',
            'mediumpurple' => '#9370db', 'mediumseagreen' => '#3cb371', 'mediumslateblue' => '#7b68ee',
            'mediumspringgreen' => '#00fa9a', 'mediumturquoise' => '#48d1cc', 'mediumvioletred' => '#c71585',
            'midnightblue' => '#191970', 'mintcream' => '#f5fffa', 'mistyrose' => '#ffe4e1',
            'moccasin' => '#ffe4b5', 'navajowhite' => '#ffdead', 'navy' => '#000080',
            'oldlace' => '#fdf5e6', 'olive' => '#808000', 'olivedrab' => '#6b8e23',
            'orange' => '#ffa500', 'orangered' => '#ff4500', 'orchid' => '#da70d6',
            'palegoldenrod' => '#eee8aa', 'palegreen' => '#98fb98', 'paleturquoise' => '#afeeee',
            'palevioletred' => '#db7093', 'papayawhip' => '#ffefd5', 'peachpuff' => '#ffdab9',
            'peru' => '#cd853f', 'pink' => '#ffc0cb', 'plum' => '#dda0dd',
            'powderblue' => '#b0e0e6', 'purple' => '#800080', 'rebeccapurple' => '#663399',
            'red' => '#ff0000', 'rosybrown' => '#bc8f8f', 'royalblue' => '#4169e1',
            'saddlebrown' => '#8b4513', 'salmon' => '#fa8072', 'sandybrown' => '#f4a460',
            'seagreen' => '#2e8b57', 'seashell' => '#fff5ee', 'sienna' => '#a0522d',
            'silver' => '#c0c0c0', 'skyblue' => '#87ceeb', 'slateblue' => '#6a5acd',
            'slategray' => '#708090', 'slategrey' => '#708090', 'snow' => '#fffafa',
            'springgreen' => '#00ff7f', 'steelblue' => '#4682b4', 'tan' => '#d2b48c',
            'teal' => '#008080', 'thistle' => '#d8bfd8', 'tomato' => '#ff6347',
            'turquoise' => '#40e0d0', 'violet' => '#ee82ee', 'wheat' => '#f5deb3',
            'white' => '#ffffff', 'whitesmoke' => '#f5f5f5', 'yellow' => '#ffff00',
            'yellowgreen' => '#9acd32',
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
