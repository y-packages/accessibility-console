<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class ImageDimensionsAndScale extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_4_4_IMAGE_DIMENSIONS_SCALE'; }
    public function getDescription(): string { return 'Images must specify explicit width and height attributes or CSS dimensions to prevent layout shifts and ensure accessible touch target sizes.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::AA; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 4; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'img') {
            return null;
        }

        $hasWidthAttr = $element->hasAttribute('width') && !empty(trim($element->getAttribute('width')));
        $hasHeightAttr = $element->hasAttribute('height') && !empty(trim($element->getAttribute('height')));
        $style = $element->getAttribute('style');

        $hasWidthStyle = preg_match('/width\s*:\s*[^;]+/i', $style) === 1;
        $hasHeightStyle = preg_match('/height\s*:\s*[^;]+/i', $style) === 1;

        if (!$hasWidthAttr && !$hasWidthStyle) {
            return $this->createViolation(
                $element,
                'Image element is missing explicit width attributes or CSS width rules.',
                'Specify explicit width and height attributes (e.g. width="300" height="200") on the <img> tag to prevent layout shifts (CLS).'
            );
        }

        if (!$hasHeightAttr && !$hasHeightStyle) {
            return $this->createViolation(
                $element,
                'Image element is missing explicit height attributes or CSS height rules.',
                'Specify explicit height attributes on the <img> tag to ensure proper rendering aspect ratio.'
            );
        }

        // Check if image target is too small when nested in a link or button
        if ($hasWidthAttr && $hasHeightAttr) {
            $w = (int)$element->getAttribute('width');
            $h = (int)$element->getAttribute('height');

            $parentTag = strtolower($element->parentNode ? $element->parentNode->nodeName : '');
            if (($parentTag === 'a' || $parentTag === 'button') && ($w < 24 || $h < 24)) {
                return $this->createViolation(
                    $element,
                    sprintf('Interactive image target dimensions (%dx%dpx) are smaller than the recommended 24x24px minimum touch target size.', $w, $h),
                    'Increase interactive image target size or add padding to meet WCAG 2.5.5 touch target size requirements.'
                );
            }
        }

        return null;
    }
}
