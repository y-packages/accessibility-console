<?php

namespace YakNet\AccessibilityConsole\AI;

use YakNet\AccessibilityConsole\Core\Violation;

interface AiProviderInterface
{
    /**
     * Get the name of this AI provider.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Suggest a fix for the given violation.
     *
     * @param Violation $violation
     * @return string|null
     */
    public function suggestFix(Violation $violation): ?string;
}
