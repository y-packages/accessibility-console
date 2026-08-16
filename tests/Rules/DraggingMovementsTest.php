<?php

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\DraggingMovements;

class DraggingMovementsTest extends TestCase
{
    public function testFlagsDraggableWithoutAlternativeControls(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DraggingMovements());

        $html = '<div><div draggable="true" class="task-card">Task 1</div></div>';
        $violations = $scanner->scan($html);

        $this->assertCount(1, $violations);
        $this->assertSame('WCAG_2_5_7_DRAGGING_MOVEMENTS', $violations[0]->ruleId);
    }

    public function testPassesDraggableWithKeyboardShortcutsOrButtons(): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new DraggingMovements());

        $html = '<div><div draggable="true" aria-keyshortcuts="Control+Up Control+Down" class="task-card">Task 1</div></div>';
        $violations = $scanner->scan($html);

        $this->assertCount(0, $violations);
    }
}
