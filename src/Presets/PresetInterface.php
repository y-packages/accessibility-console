<?php

namespace YakNet\AccessibilityConsole\Presets;

interface PresetInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getLevel(): int;

    /**
     * @return string[]
     */
    public function getIncludedRules(): array;

    /**
     * @return string[]
     */
    public function getExcludedRules(): array;
}
