<?php

// 1. Setup Autoloading (Mocking Composer)
require_once __DIR__ . '/../src/Scanner.php';
require_once __DIR__ . '/../src/Violation.php';
require_once __DIR__ . '/../src/SourceLocator.php';
require_once __DIR__ . '/../src/Reporter/HtmlReporter.php';
require_once __DIR__ . '/../src/Rule/RuleInterface.php';
require_once __DIR__ . '/../src/Rules/ImgAltText.php';
require_once __DIR__ . '/../src/Rules/FormLabel.php';
require_once __DIR__ . '/../src/Rules/HtmlHasLang.php';
require_once __DIR__ . '/../src/Rules/EmptyLink.php';

use YakNet\AccessibilityConsole\Scanner;
use YakNet\AccessibilityConsole\SourceLocator;
use YakNet\AccessibilityConsole\Reporter\HtmlReporter;
use YakNet\AccessibilityConsole\Rules\ImgAltText;
use YakNet\AccessibilityConsole\Rules\FormLabel;

// 2. Start Buffering to capture output
ob_start();

// 3. Simple Router
$page = $_GET['page'] ?? 'home';
$viewFile = __DIR__ . "/views/{$page}.php";

if (!file_exists($viewFile)) {
    $viewFile = __DIR__ . '/views/home.php';
}

// 4. Render Layout (which includes the view)
$viewPath = $viewFile;
include __DIR__ . '/views/layout.php';

// 5. Capture Output
$html = ob_get_clean();

// 6. RUN ACCESSIBILITY SCANNER
// ==========================================
$scanner = new Scanner();
$scanner->addRule(new ImgAltText());
$scanner->addRule(new FormLabel());
$scanner->addRule(new \YakNet\AccessibilityConsole\Rules\HtmlHasLang());
$scanner->addRule(new \YakNet\AccessibilityConsole\Rules\EmptyLink());

// Scan the captured HTML
$violations = $scanner->scan($html);

if (!empty($violations)) {
    // Locate Sources
    // We point it to the 'views' directory so it knows where to look for the error source
    $locator = new SourceLocator(__DIR__ . '/views');

    foreach ($violations as $violation) {
        $location = $locator->locate($violation->snippet);
        if ($location) {
            $violation->setSourceLocation($location['file'], $location['line']);
        }
    }

    // Render Report
    $reporter = new HtmlReporter();
    $reportHtml = $reporter->render($violations);

    // Inject into body
    $html = str_replace('</body>', $reportHtml . '</body>', $html);
}

// 7. Output Final HTML
echo $html;
