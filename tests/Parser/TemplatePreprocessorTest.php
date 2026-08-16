<?php

namespace YakNet\AccessibilityConsole\Tests\Parser;

use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Parser\TemplatePreprocessor;

class TemplatePreprocessorTest extends TestCase
{
    private TemplatePreprocessor $preprocessor;

    protected function setUp(): void
    {
        $this->preprocessor = new TemplatePreprocessor();
    }

    public function testPreprocessesBladeTemplate(): void
    {
        $blade = "<div>\n@if(true)\n<h1>{{ title }}</h1>\n@endif\n</div>";
        $processed = $this->preprocessor->preprocess($blade, 'views/user.blade.php');

        $this->assertStringNotContainsString('@if', $processed);
        $this->assertStringNotContainsString('{{', $processed);
        $this->assertStringContainsString('<h1>', $processed);

        // Verify line count is preserved
        $this->assertSame(substr_count($blade, "\n"), substr_count($processed, "\n"));
    }

    public function testPreprocessesTwigTemplate(): void
    {
        $twig = "<div>\n{% if logged_in %}\n<p>{{ user.email }}</p>\n{% endif %}\n</div>";
        $processed = $this->preprocessor->preprocess($twig, 'templates/user.twig');

        $this->assertStringNotContainsString('{%', $processed);
        $this->assertStringNotContainsString('{{', $processed);
        $this->assertStringContainsString('<p>', $processed);

        // Verify line count is preserved
        $this->assertSame(substr_count($twig, "\n"), substr_count($processed, "\n"));
    }

    public function testPreprocessesPhpTemplate(): void
    {
        $php = "<div>\n<?php if (true): ?>\n<button><?= \$label ?></button>\n<?php endif; ?>\n</div>";
        $processed = $this->preprocessor->preprocess($php, 'views/button.php');

        $this->assertStringNotContainsString('<?php', $processed);
        $this->assertStringNotContainsString('<?=', $processed);
        $this->assertStringContainsString('<button>', $processed);

        // Verify line count is preserved
        $this->assertSame(substr_count($php, "\n"), substr_count($processed, "\n"));
    }
}
