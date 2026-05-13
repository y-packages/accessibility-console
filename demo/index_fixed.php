<?php

// 1. Setup Autoloading
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
use YakNet\AccessibilityConsole\Rules\HtmlHasLang;
use YakNet\AccessibilityConsole\Rules\EmptyLink;

// 2. Start Buffering
ob_start();

// 3. Simple Router for Fixed Views
$page = $_GET['page'] ?? 'home';
$viewFile = __DIR__ . "/views-fixed/{$page}.php";

if (!file_exists($viewFile)) {
    $viewFile = __DIR__ . '/views-fixed/home.php';
}

// 4. Render Layout
$viewPath = $viewFile;
include __DIR__ . '/views-fixed/layout.php';

// 5. Capture Output
$html = ob_get_clean();

// 6. RUN ACCESSIBILITY SCANNER
$scanner = new Scanner();
$scanner->addRule(new ImgAltText());
$scanner->addRule(new FormLabel());
$scanner->addRule(new HtmlHasLang());
$scanner->addRule(new EmptyLink());

$violations = $scanner->scan($html);

if (!empty($violations)) {
    // Locate Sources
    $locator = new SourceLocator(__DIR__ . '/views-fixed');

    foreach ($violations as $violation) {
        $location = $locator->locate($violation->snippet);
        if ($location) {
            $violation->setSourceLocation($location['file'], $location['line']);
        }
    }

    $reporter = new HtmlReporter();
    // Enable success widget for this demo
    $reporter->setShowSuccess(true);

    $reportHtml = $reporter->render($violations);
    $html = str_replace('</body>', $reportHtml . '</body>', $html);
} else {
    // Violations is empty, but render() will return the success widget because we set it to true
    $reporter = new HtmlReporter();
    $reporter->setShowSuccess(true);
    $reportHtml = $reporter->render($violations);

    $html = str_replace('</body>', $reportHtml . '</body>', $html);
}

echo $html;
