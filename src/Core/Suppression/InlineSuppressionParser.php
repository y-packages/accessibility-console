<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Core\Suppression;

/**
 * Parses inline accessibility suppression directives from HTML/Blade/Twig/Vue/JSX files.
 *
 * Supported directives:
 *   <!-- a11y-disable-file [RULE_1, RULE_2] [-- reason] -->
 *   <!-- a11y-disable-next-line [RULE_1, RULE_2] [-- reason] -->
 *   <!-- a11y-disable-line [RULE_1, RULE_2] [-- reason] -->
 *   <!-- a11y-disable [RULE_1, RULE_2] [-- reason] -->
 *   <!-- a11y-enable [RULE_1, RULE_2] -->
 */
class InlineSuppressionParser
{
    /**
     * @return array{
     *     fileDisabled: bool,
     *     fileRules: array<string, string>,
     *     lineRules: array<int, array<string, string>>
     * }
     */
    public function parse(string $sourceContent): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $sourceContent);
        if (!is_array($lines)) {
            return [
                'fileDisabled' => false,
                'fileRules' => [],
                'lineRules' => [],
            ];
        }

        $fileDisabled = false;
        /** @var array<string, string> $fileRules */
        $fileRules = [];
        /** @var array<int, array<string, string>> $lineRules */
        $lineRules = [];
        /** @var array<string, string> $activeBlockRules */
        $activeBlockRules = [];
        $blockAllReason = null;

        foreach ($lines as $idx => $lineText) {
            $lineNum = $idx + 1;

            // Apply currently active block suppressions to this line
            if ($blockAllReason !== null) {
                $lineRules[$lineNum]['*'] = $blockAllReason;
            }
            foreach ($activeBlockRules as $ruleId => $reason) {
                $lineRules[$lineNum][$ruleId] = $reason;
            }

            if (preg_match_all('/<!--\s*a11y-(disable-file|disable-next-line|disable-line|disable|enable)\b(.*?)-->/i', $lineText, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $directive = strtolower($match[1]);
                    $body = trim($match[2]);
                    [$rules, $reason] = $this->parseDirectiveBody($body);

                    if ($directive === 'disable-file') {
                        if (empty($rules)) {
                            $fileDisabled = true;
                            $fileRules['*'] = $reason;
                        } else {
                            foreach ($rules as $r) {
                                $fileRules[$r] = $reason;
                            }
                        }
                    } elseif ($directive === 'disable-next-line') {
                        $nextLine = $lineNum + 1;
                        if (empty($rules)) {
                            $lineRules[$nextLine]['*'] = $reason;
                        } else {
                            foreach ($rules as $r) {
                                $lineRules[$nextLine][$r] = $reason;
                            }
                        }
                    } elseif ($directive === 'disable-line') {
                        if (empty($rules)) {
                            $lineRules[$lineNum]['*'] = $reason;
                        } else {
                            foreach ($rules as $r) {
                                $lineRules[$lineNum][$r] = $reason;
                            }
                        }
                    } elseif ($directive === 'disable') {
                        if (empty($rules)) {
                            $blockAllReason = $reason;
                            $lineRules[$lineNum]['*'] = $reason;
                        } else {
                            foreach ($rules as $r) {
                                $activeBlockRules[$r] = $reason;
                                $lineRules[$lineNum][$r] = $reason;
                            }
                        }
                    } elseif ($directive === 'enable') {
                        if (empty($rules)) {
                            $blockAllReason = null;
                            $activeBlockRules = [];
                        } else {
                            foreach ($rules as $r) {
                                unset($activeBlockRules[$r]);
                            }
                        }
                    }
                }
            }
        }

        return [
            'fileDisabled' => $fileDisabled,
            'fileRules' => $fileRules,
            'lineRules' => $lineRules,
        ];
    }

    /**
     * Check if a specific rule at a given line is suppressed, returning the reason string or null if not suppressed.
     *
     * @param array{fileDisabled: bool, fileRules: array<string, string>, lineRules: array<int, array<string, string>>} $suppressions
     */
    public function getSuppressionReason(array $suppressions, string $ruleId, int $line): ?string
    {
        if ($suppressions['fileDisabled']) {
            return $suppressions['fileRules']['*'] ?? '';
        }

        if (isset($suppressions['fileRules'][$ruleId])) {
            return $suppressions['fileRules'][$ruleId];
        }

        if (isset($suppressions['lineRules'][$line]['*'])) {
            return $suppressions['lineRules'][$line]['*'];
        }

        if (isset($suppressions['lineRules'][$line][$ruleId])) {
            return $suppressions['lineRules'][$line][$ruleId];
        }

        return null;
    }

    /**
     * Parse optional comma/space separated rule IDs and optional "-- reason" suffix.
     *
     * @return array{0: string[], 1: string}
     */
    private function parseDirectiveBody(string $body): array
    {
        $reason = '';
        $rulesPart = $body;

        if (str_contains($body, '--')) {
            $parts = explode('--', $body, 2);
            $rulesPart = trim($parts[0]);
            $reason = trim($parts[1]);
        }

        if ($rulesPart === '') {
            return [[], $reason];
        }

        $tokens = preg_split('/[\s,]+/', $rulesPart);
        if (!is_array($tokens)) {
            return [[], $reason];
        }

        $rules = [];
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token !== '') {
                $rules[] = $token;
            }
        }

        return [$rules, $reason];
    }
}
