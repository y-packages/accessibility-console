<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use YakNet\AccessibilityConsole\Core\AbstractRule;
use YakNet\AccessibilityConsole\Core\Severity;
use YakNet\AccessibilityConsole\Core\WCAGStandard;

class AccesskeyDuplicate extends AbstractRule
{
    public function getId(): string
    {
        return 'WCAG_2_1_1_ACCESSKEY_DUPLICATE';
    }

    public function getDescription(): string
    {
        return 'accesskey attribute values must be unique across the document.';
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
        return 2;
    }

    public function check(\DOMDocument $doc): array
    {
        $violations = [];
        $xpath = new \DOMXPath($doc);
        $elements = $xpath->query('//*[@accesskey]');

        if ($elements === false || $elements->length === 0) {
            return [];
        }

        $keys = [];
        foreach ($elements as $el) {
            if (!$el instanceof DOMElement) {
                continue;
            }
            $val = trim($el->getAttribute('accesskey'));
            if ($val === '') {
                continue;
            }
            if (!isset($keys[$val])) {
                $keys[$val] = [];
            }
            $keys[$val][] = $el;
        }

        foreach ($keys as $key => $elementsWithKey) {
            if (count($elementsWithKey) > 1) {
                foreach ($elementsWithKey as $el) {
                    $violations[] = $this->createViolation(
                        "Duplicate accesskey=\"{$key}\" found. Access keys must be unique across the document.",
                        $el,
                        "Assign a unique accesskey to each interactive element, or avoid using accesskey entirely."
                    );
                }
            }
        }

        return $violations;
    }
}
