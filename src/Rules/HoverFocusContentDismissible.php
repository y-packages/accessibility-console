<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\Violation;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class HoverFocusContentDismissible extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_4_13_CONTENT_ON_HOVER';
    }

    public function getDescription(): string
    {
        return 'Tooltip elements (role="tooltip" or data-tooltip) must be programmatically linked via aria-describedby and keyboard accessible (WCAG 1.4.13).';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::AA;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 4;
    }

    public function check(DOMElement $element): ?Violation
    {
        $role = strtolower(trim($element->getAttribute('role')));
        $tag = strtolower($element->tagName);

        // Case 1: role="tooltip" without an id or without any trigger referencing it
        if ($role === 'tooltip') {
            $id = trim($element->getAttribute('id'));
            if ($id === '') {
                return $this->createViolation(
                    $element,
                    'Element with role="tooltip" is missing an id attribute and cannot be referenced by aria-describedby (WCAG 1.4.13).',
                    'Add a unique id to the tooltip element and link the trigger control with aria-describedby="<tooltip-id>".'
                );
            }

            $doc = $element->ownerDocument;
            if ($doc !== null) {
                $xpath = new DOMXPath($doc);
                $triggers = $xpath->query('//*[@aria-describedby]');
                $isReferenced = false;
                if ($triggers !== false) {
                    foreach ($triggers as $trigger) {
                        if ($trigger instanceof DOMElement) {
                            $refs = preg_split('/\s+/', trim($trigger->getAttribute('aria-describedby')));
                            if (is_array($refs) && in_array($id, $refs, true)) {
                                $isReferenced = true;
                                break;
                            }
                        }
                    }
                }
                if (!$isReferenced) {
                    return $this->createViolation(
                        $element,
                        sprintf('Tooltip with id="%s" is not referenced by any element via aria-describedby (WCAG 1.4.13).', $id),
                        sprintf('Add aria-describedby="%s" to the focusable trigger element controlling this tooltip.', $id)
                    );
                }
            }

            return null;
        }

        // Case 2: Non-focusable element with data-tooltip / data-bs-toggle="tooltip"
        $hasDataTooltip = $element->hasAttribute('data-tooltip')
            || strtolower($element->getAttribute('data-bs-toggle')) === 'tooltip'
            || strtolower($element->getAttribute('data-toggle')) === 'tooltip';

        if ($hasDataTooltip) {
            $nativelyFocusable = in_array($tag, ['a', 'button', 'input', 'select', 'textarea', 'summary'], true);
            $hasTabindex = $element->hasAttribute('tabindex') && (int)$element->getAttribute('tabindex') >= 0;

            if (!$nativelyFocusable && !$hasTabindex) {
                return $this->createViolation(
                    $element,
                    sprintf('Non-focusable <%s> element triggers a hover tooltip without keyboard focus support (WCAG 1.4.13).', $tag),
                    'Use a native <button> or add tabindex="0" and Escape-key dismissal so keyboard users can access and dismiss the tooltip.'
                );
            }
        }

        return null;
    }
}
