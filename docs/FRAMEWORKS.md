# 🛠️ Framework & CMS Integrations Guide

## 1. Laravel Integration

### Middleware Registration (`app/Http/Kernel.php` or `bootstrap/app.php`)
```php
use YakNet\AccessibilityConsole\Stubs\Laravel\AccessibilityMiddleware;

$middleware->web(append: [
    AccessibilityMiddleware::class,
]);
```

### Automated PHPUnit / Pest Testing
```php
use YakNet\AccessibilityConsole\Stubs\Laravel\AssertsAccessibility;

class ExampleTest extends TestCase
{
    use AssertsAccessibility;

    public function test_homepage_is_accessible(): void
    {
        $this->assertPageIsAccessible('https://localhost', minScore: 90);
    }
}
```

## 2. WordPress Integration

Drop `stubs/wordpress/yaknet-a11y-wp-plugin.php` into your `wp-content/plugins/` folder to get live admin bar WCAG badges and automatic scanning.
