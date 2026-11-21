# Contributing to YakNet Accessibility Console

Thank you for your interest in contributing to **YakNet Accessibility Console**, an open-source accessibility analysis library maintained by **YakNet**.

## Development Setup

```bash
git clone https://github.com/y-packages/accessibility-console.git
cd accessibility-console
composer install
```

## Running Tests & Static Analysis

All pull requests must pass both the full PHPUnit test suite and PHPStan Level 9 with zero errors:

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse -l 9 src/
php bin/a11y doctor
```

## Adding a New Accessibility Rule

1. Create a rule class under `src/Rules/` extending `YakNet\AccessibilityConsole\Rules\AbstractRule` (element-level) or `YakNet\AccessibilityConsole\Core\AbstractRule` (document-level).
2. Assign a unique `getId()` following the `WCAG_<P>_<G>_<C>_<NAME>` convention.
3. Provide accurate `getDescription()`, `getStandard()`, `getSeverity()`, and `getLevel()` metadata.
4. Add unit tests covering both non-compliant and compliant markup in `tests/Rules/`.
5. Verify rule registry integrity with `php bin/a11y doctor`.

## Coding Standards

- PHP 8.2+ with `declare(strict_types=1);` in new files.
- Full type annotations compatible with PHPStan Level 9.
- Keep documentation and user-facing messages clear, open-source friendly, and free of vendor lock-in.

## Pull Request Process

1. Fork the repository and create a feature branch (`git checkout -b feature/my-improvement`).
2. Write clear, focused commits.
3. Ensure `phpunit`, `phpstan`, and `bin/a11y doctor` all pass cleanly.
4. Open a Pull Request describing the motivation and test coverage.
