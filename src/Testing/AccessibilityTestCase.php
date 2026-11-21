<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Testing;

use PHPUnit\Framework\TestCase;

/**
 * Convenience PHPUnit base TestCase pre-wired with YakNet AccessibilityAssertions.
 */
abstract class AccessibilityTestCase extends TestCase
{
    use AccessibilityAssertions;
}
