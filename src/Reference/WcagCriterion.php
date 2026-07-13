<?php

namespace YakNet\AccessibilityConsole\Reference;

class WcagCriterion
{
    public function __construct(
        public string $id,
        public string $title,
        public string $level,
        public string $url,
        public string $description,
        public string $badExample,
        public string $goodExample
    ) {}

    /**
     * @return array{id: string, title: string, level: string, url: string, description: string, badExample: string, goodExample: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'level' => $this->level,
            'url' => $this->url,
            'description' => $this->description,
            'badExample' => $this->badExample,
            'goodExample' => $this->goodExample,
        ];
    }
}
