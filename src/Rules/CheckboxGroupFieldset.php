<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Rules;

use DOMDocument;
use DOMElement;
use DOMXPath;
use YakNet\AccessibilityConsole\Core\AbstractRule as DocumentAbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class CheckboxGroupFieldset extends DocumentAbstractRule
{
    public function getId(): string
    {
        return 'WCAG_1_3_1_CHECKBOX_GROUP';
    }

    public function getDescription(): string
    {
        return 'Multiple checkboxes sharing the same group name should be grouped inside a <fieldset> with a <legend> or an element with role="group".';
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::WARNING;
    }

    public function getLevel(): int
    {
        return 3;
    }

    public function check(DOMDocument $doc): array
    {
        $xpath = new DOMXPath($doc);
        $checkboxes = $xpath->query('//input[@type="checkbox" and @name]');
        if ($checkboxes === false || $checkboxes->length < 2) {
            return [];
        }

        /** @var array<string, DOMElement[]> $groups */
        $groups = [];
        foreach ($checkboxes as $cb) {
            if (!$cb instanceof DOMElement) {
                continue;
            }
            $name = trim($cb->getAttribute('name'));
            if ($name === '') {
                continue;
            }
            $groups[$name][] = $cb;
        }

        $violations = [];
        foreach ($groups as $name => $elements) {
            if (count($elements) < 2) {
                continue;
            }

            $first = $elements[0];
            $isGrouped = false;
            $parent = $first->parentNode;
            while ($parent instanceof DOMElement) {
                $pTag = strtolower($parent->tagName);
                $pRole = strtolower(trim($parent->getAttribute('role')));
                if ($pTag === 'fieldset' || $pRole === 'group') {
                    $isGrouped = true;
                    break;
                }
                $parent = $parent->parentNode;
            }

            if (!$isGrouped) {
                $violations[] = $this->createViolation(
                    sprintf('Checkbox group "%s" (%d checkboxes) is not wrapped in a <fieldset><legend> or role="group" container.', $name, count($elements)),
                    $first,
                    'Wrap related checkboxes inside a <fieldset> with a descriptive <legend> (or a container with role="group" and aria-labelledby).'
                );
            }
        }

        return $violations;
    }
}
