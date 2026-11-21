# Changelog

All notable changes to **YakNet Accessibility Console** are documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## [4.0.0] - 2026-11-21

### Added
- **45 New Accessibility Rules (130 → 175 Total Rules):**
  - WCAG 2.2 & interaction rules: `FocusAppearance` (2.4.13), `ConsistentHelp` (3.2.6), `MotionAnimationReduced` (2.3.3), `OrientationLockBanned` (1.3.4), `HoverFocusContentDismissible` (1.4.13), `CharacterKeyShortcuts` (2.1.4), `PointerCancellation` (2.5.2), `TimeoutWarning` (2.2.1), `FlashingContentBanned` (2.3.1), `TargetSpacingMinimum` (2.5.8).
  - WAI-ARIA 1.2 & APG widget rules: `AriaTablistPattern`, `AriaComboboxPattern`, `AriaSwitchState`, `AriaSliderAttributes`, `AriaMenuPattern`, `AriaTreePattern`, `AriaBreadcrumbPattern`, `AriaModalBackdrop`, `AriaLivePoliteMisuse`, `AriaOwnsValidTarget`, `AriaActivedescendantValid`, `AriaDeprecatedRoles`.
  - Semantic HTML5 & landmark rules: `ArticleAccessibleHeading`, `SectionAccessibleName`, `NavAccessibleName`, `HeaderMainNestedInvalid`, `HeadingLengthConcise`, `AbbreviationExpansion`, `BlockquoteCiteValid`, `TimeDatetimeValid`, `IframeSandboxAccessibility`, `DocumentHasH1`.
  - Forms & cognitive rules: `InputTypeValidation`, `FormErrorSummary`, `CheckboxGroupFieldset`, `SelectOptionNotEmpty`, `TextareaAccessibleName`, `OutputForValid`, `ButtonTypeExplicit`, `LinkDownloadContext`.
  - Typography & visual rules: `ColorContrastAAA` (1.4.6), `FontSizeMinimum`, `AllCapsLongText`, `JustifiedTextBanned`, `SvgFocusableIeEdge`.
- **VPAT 2.5 / ACR Generator (`src/Compliance/VpatReportGenerator.php` & `a11y vpat`):** Markdown, HTML, and JSON conformance reports aligned with WCAG 2.1/2.2, Revised Section 508, and EN 301 549.
- **8 New Regulatory & Sectoral Presets (3 → 11 Total):** `wcag22-aa`, `wcag22-aaa`, `eaa2025`, `bitv2`, `government`, `healthcare`, `banking`, `education`.
- **Policy Governance Engine (`src/Policy/PolicyEngine.php`):** Enforces `min_score`, `max_critical`, `max_errors`, `max_warnings`, `required_standards`, `banned_rules`, `severity_overrides`, and `principle_limits` from `a11y.yaml`.
- **Inline Suppression Directives (`src/Core/Suppression/InlineSuppressionParser.php`):** Supports `a11y-disable-next-line`, `a11y-disable-line`, `a11y-disable` / `a11y-enable`, and `a11y-disable-file` with audit trail logging.
- **Incremental Content-Hash Cache (`src/Cache/ScanCache.php`):** `--cache` & `--clear-cache` flags skip unchanged templates on repeat scans.
- **CI/CD Workflow Generator (`src/CI/CiWorkflowGenerator.php`):** `a11y init --ci=github|gitlab|azure|bitbucket` generates ready-to-use CI pipelines.
- **PHPUnit Testing Trait (`src/Testing/AccessibilityAssertions.php`):** Drop-in assertions (`assertHtmlAccessible`, `assertAccessibilityScoreAtLeast`, `assertMeetsAccessibilityPreset`, `assertNoAccessibilityRuleViolation`).
- **History Tracking & Regression Diff (`src/Analytics/HistoryTracker.php` & `a11y compare`):** Trend snapshots (`--save-history`) and PR regression comparisons (`--fail-on-regression`).
- **Vue SFC & React JSX/TSX Parsers (`VueParser`, `JsxParser`):** Native support for `.vue`, `.jsx`, and `.tsx` templates with 1:1 line mapping.
- **New CLI Commands (5 → 10 Total):** `vpat`, `compare`, `doctor`, `explain`, `presets`.
- **Checkstyle XML Formatter & Expanded Webhooks:** `CheckstyleFormatter`, `MicrosoftTeamsNotifier`, and HMAC-SHA256 signed `GenericWebhookNotifier`.
- **Expanded Offline Self-Healing:** 32 deterministic offline auto-fixes in `LocalHealer`.
- **Rule Execution Profiling:** `--profile` option powered by `RuleExecutionStats` inside `RuleEngine`.

### Changed
- Standardized open-source organization metadata to **YakNet** across documentation, license, and report exporters.
- Fixed preset rule filtering in `ScanCommand` so preset-included and preset-excluded rules are fully enforced.
- Expanded `WcagDatabase` with WCAG 2.1/2.2 guidance and automatic prefix fallback lookup.

## [3.3.0] - 2026-04-01
- Added `LabelInName`, `RedundantEntry`, `TextSpacing` rules, SVG badges, initial presets, and Slack/Discord notifiers.
