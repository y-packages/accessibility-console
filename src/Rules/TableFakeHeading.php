<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class TableFakeHeading extends AbstractRule
{
    public function getId(): string { return 'WCAG_1_3_1_FAKE_HEADING'; }
    public function getDescription(): string { return 'Detects <td> elements that appear to be headers visually.'; }
    public function getStandard(): WCAGStandard { return WCAGStandard::A; }
    public function getSeverity(): Severity { return Severity::WARNING; }
    public function getLevel(): int { return 5; }

    public function check(DOMElement $element): ?Violation
    {
        if (strtolower($element->tagName) !== 'td') {
            return null;
        }

        $text = trim($element->textContent);
        if (empty($text) || strlen($text) > 50) {
            return null;
        }

        $style = $element->getAttribute('style');
        $hasBoldStyle = preg_match('/font-weight:\s*(bold|[789]00)/i', $style);
        
        $doc = $element->ownerDocument;
        if ($doc === null) {
            return null;
        }
        $xpath = new DOMXPath($doc);
        $strongOrB = $xpath->query('.//b | .//strong', $element);
        
        $hasBoldChild = false;
        if ($strongOrB !== false) {
            foreach ($strongOrB as $node) {
                if ($node instanceof \DOMNode && trim($node->textContent) === $text) {
                    $hasBoldChild = true;
                    break;
                }
            }
        }

        if ($hasBoldStyle || $hasBoldChild) {
            return $this->createViolation(
                $element,
                'Başlık gibi biçimlendirilmiş <td> öğesi bulundu, bunun yerine <th> kullanılmalıdır. / <td> styled as a heading found, use <th> instead.',
                'Kalın yazılmış hücre metnini <th> etiketi ile değiştirin. / Change the bolded <td> cell to a <th> element.'
            );
        }

        return null;
    }
}
