# ♿ YakNet Accessibility Console — WCAG 2.1 Rules Reference

YakNet Accessibility Console features **110 automated WCAG 2.1 rules** across Levels A, AA, and AAA.

## Rule Categories

### 1. ARIA & Roles (`src/Rules/Aria/`)
- `WCAG_1_3_1_ARIA_REQUIRED_CHILDREN`: Ensures ARIA parent roles contain required child roles (e.g. `role="radiogroup"`).
- `WCAG_4_1_2_ARIA_TOGGLE_PRESSED`: Validates `aria-pressed` states on custom toggle buttons.

### 2. Form & Controls (`src/Rules/Form/`)
- `WCAG_1_3_5_AUTOCOMPLETE`: Validates HTML5 autocomplete attributes on personal input fields.
- `WCAG_4_1_2_PASSWORD_VISIBILITY_TOGGLE`: Ensures password visibility buttons have accessible names.

### 3. Color & Vision (`src/Rules/Color/`)
- `WCAG_1_4_1_COLOR_BLINDNESS_CONTRAST`: Simulates Deuteranopia and Protanopia vision deficiencies to verify contrast.
- `WCAG_1_4_3_CONTRAST`: Verifies minimum 4.5:1 text-to-background contrast ratio.

### 4. Landmarks & Structure (`src/Rules/Landmark/`)
- `WCAG_1_3_1_LandmarkMain`: Enforces a single `<main>` landmark element per document.
- `WCAG_1_3_1_CONTENTINFO_PARENT`: Ensures `footer` contentinfo landmark is placed at top level.

### 5. Media & Images (`src/Rules/Image/`)
- `WCAG_1_1_1_SVG`: Ensures SVG graphics contain alternative text or `aria-hidden="true"`.
- `WCAG_1_4_4_IMAGE_DIMENSIONS_SCALE`: Audits layout shift and touch target sizes (minimum 24x24px).
