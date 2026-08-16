<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class DuplicateId extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_4_1_2_DUPLICATE_ID';
    }

    public function getDescription(): string
    {
        return 'Element IDs must be unique across the document.';
    }

    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $xpath = new \DOMXPath($doc);
        $elements = $xpath->query('//*[@id]');
        if ($elements === false) {
            return [];
        }
        
        $ids = [];
        foreach ($elements as $el) {
            if (!$el instanceof \DOMElement) {
                continue;
            }
            $id = $el->getAttribute('id');
            if ($id === '') {
                continue;
            }
            if (!isset($ids[$id])) {
                $ids[$id] = [];
            }
            $ids[$id][] = $el;
        }

        foreach ($ids as $id => $elementsWithId) {
            if (count($elementsWithId) > 1) {
                foreach ($elementsWithId as $el) {
                    $violations[] = $this->createViolation(
                        "Duplicate ID \"{$id}\" found. Element ID attribute must be unique across the document.",
                        $el,
                        "Change duplicate id=\"{$id}\" to a unique identifier."
                    );
                }
            }
        }

        return $violations;
    }

    public function getStandard(): WCAGStandard
    {
        return WCAGStandard::A;
    }

    public function getSeverity(): Severity
    {
        return Severity::ERROR;
    }

    public function getLevel(): int
    {
        return 3;
    }
}
