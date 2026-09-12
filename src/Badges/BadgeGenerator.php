<?php

namespace YakNet\AccessibilityConsole\Badges;

class BadgeGenerator
{
    /**
     * Generate an SVG badge representing the Accessibility Health Score.
     *
     * @param int $score 0-100
     * @param BadgeOptions|null $options
     * @return string Raw SVG markup
     */
    public static function generateScoreBadge(int $score, ?BadgeOptions $options = null): string
    {
        $opts = $options ?? new BadgeOptions();
        $color = $opts->rightColor ?? ($score >= 90 ? '#10b981' : ($score >= 70 ? '#f59e0b' : '#ef4444'));
        $label = $opts->label;
        $value = "{$score}/100";

        return self::renderBadge($label, $value, $color, $opts);
    }

    /**
     * Generate an SVG badge representing WCAG compliance status.
     *
     * @param string $standard E.g. 'WCAG 2.1 AA'
     * @param bool $compliant
     * @param BadgeOptions|null $options
     * @return string Raw SVG markup
     */
    public static function generateComplianceBadge(string $standard = 'WCAG 2.1 AA', bool $compliant = true, ?BadgeOptions $options = null): string
    {
        $opts = $options ?? new BadgeOptions();
        $color = $opts->rightColor ?? ($compliant ? '#10b981' : '#ef4444');
        $value = $compliant ? 'passed' : 'failed';

        return self::renderBadge($standard, $value, $color, $opts);
    }

    /**
     * Generate an SVG badge for total violations count.
     *
     * @param int $violationsCount
     * @param BadgeOptions|null $options
     * @return string Raw SVG markup
     */
    public static function generateViolationsBadge(int $violationsCount, ?BadgeOptions $options = null): string
    {
        $opts = $options ?? new BadgeOptions();
        $color = $opts->rightColor ?? ($violationsCount === 0 ? '#10b981' : ($violationsCount < 5 ? '#f59e0b' : '#ef4444'));
        $value = $violationsCount === 0 ? '0 issues' : "{$violationsCount} issues";
        $label = $opts->label !== 'accessibility' ? $opts->label : 'a11y issues';

        return self::renderBadge($label, $value, $color, $opts);
    }

    /**
     * Render a standard flat shield SVG badge.
     */
    private static function renderBadge(string $label, string $value, string $rightColor, ?BadgeOptions $options = null): string
    {
        $opts = $options ?? new BadgeOptions();
        $charWidth = 7;
        $padding = 12;
        $leftWidth = (int)(mb_strlen($label) * $charWidth + $padding);
        $rightWidth = (int)(mb_strlen($value) * $charWidth + $padding);
        $totalWidth = $leftWidth + $rightWidth;
        $height = 20;

        $leftCenter = (int)($leftWidth / 2);
        $rightCenter = (int)($leftWidth + ($rightWidth / 2));

        $escLabel = htmlspecialchars($label);
        $escValue = htmlspecialchars($value);
        $borderRadius = $opts->borderRadius;
        $leftColor = $opts->leftColor;
        $fontFamily = htmlspecialchars($opts->fontFamily);
        $fontSize = $opts->fontSize;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$totalWidth}" height="{$height}" role="img" aria-label="{$escLabel}: {$escValue}">
  <title>{$escLabel}: {$escValue}</title>
  <linearGradient id="s" x2="0" y2="100%">
    <stop offset="0" stop-color="#bbb" stop-opacity=".1"/>
    <stop offset="1" stop-opacity=".1"/>
  </linearGradient>
  <clipPath id="r">
    <rect width="{$totalWidth}" height="{$height}" rx="{$borderRadius}" fill="#fff"/>
  </clipPath>
  <g clip-path="url(#r)">
    <rect width="{$leftWidth}" height="{$height}" fill="{$leftColor}"/>
    <rect x="{$leftWidth}" width="{$rightWidth}" height="{$height}" fill="{$rightColor}"/>
    <rect width="{$totalWidth}" height="{$height}" fill="url(#s)"/>
  </g>
  <g fill="#fff" text-anchor="middle" font-family="{$fontFamily}" text-rendering="geometricPrecision" font-size="{$fontSize}">
    <text aria-hidden="true" x="{$leftCenter}" y="15" fill="#010101" fill-opacity=".3">{$escLabel}</text>
    <text x="{$leftCenter}" y="14">{$escLabel}</text>
    <text aria-hidden="true" x="{$rightCenter}" y="15" fill="#010101" fill-opacity=".3">{$escValue}</text>
    <text x="{$rightCenter}" y="14">{$escValue}</text>
  </g>
</svg>
SVG;
    }
}
