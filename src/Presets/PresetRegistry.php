<?php

namespace YakNet\AccessibilityConsole\Presets;

class PresetRegistry
{
    /**
     * @var array<string, PresetInterface>
     */
    private array $presets = [];

    public function __construct()
    {
        $this->register(new Section508Preset());
        $this->register(new En301549Preset());
        $this->register(new ECommercePreset());
        $this->register(new Wcag22AAPreset());
        $this->register(new Wcag22AAAPreset());
        $this->register(new Eaa2025Preset());
        $this->register(new Bitv2Preset());
        $this->register(new GovernmentPreset());
        $this->register(new HealthcarePreset());
        $this->register(new BankingPreset());
        $this->register(new EducationPreset());
    }

    public function register(PresetInterface $preset): void
    {
        $this->presets[strtolower($preset->getName())] = $preset;
    }

    public function get(string $name): ?PresetInterface
    {
        return $this->presets[strtolower($name)] ?? null;
    }

    /**
     * @return array<string, PresetInterface>
     */
    public function all(): array
    {
        return $this->presets;
    }
}
