<?php

// 1. Setup Autoloading
require_once __DIR__ . '/../vendor/autoload.php';

use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Source\SourceLocator;
use YakNet\AccessibilityConsole\Reporting\HtmlDashboardReporter;
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
    $locator = new SourceLocator(__DIR__ . '/views');

    foreach ($violations as $violation) {
        $location = $locator->locate($violation->htmlSnippet);
        if ($location) {
            $violation->location = $location;
        }
    }

    // Render Report (Format as results mapped by page URL)
    $reporter = new HtmlDashboardReporter();
    $reportHtml = $reporter->render(['http://localhost:8080/index.php' => $violations], 'demo/index.php');

    // Inject into body
    $html = str_replace('</body>', $reportHtml . '</body>', $html);
}

// 7. Output Final HTML
echo $html;
