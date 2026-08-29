---
name: "Frontend Conventions"
description: "Use when editing frontend markup or styles in this repository. Covers BEM naming, button and inline SVG icon conventions, and 8px/0.5rem spacing rhythm."
applyTo:
  - "**/*.scss"
  - "**/*.css"
  - "**/*.php"
  - "**/*.js"
---
# Frontend Conventions

## BEM
- Prefer BEM naming for new classes.
- Keep selectors shallow and component-scoped.
- Use modifiers for variants and stateful style differences.

## Spacing system
- Default spacing rhythm is 8px increments.
- In rem units, prefer 0.5rem step increments for layout spacing.
- For off-grid legacy values, round to nearest increment unless a specific visual exception is required.

## Button system
- Follow shared button patterns and existing variant classes.
- Preserve consistent rest/hover/focus-visible/active behavior.
- Avoid accidental width/height jumps between states.

## SVG icons
- Prefer inline SVG where existing patterns use inline SVG.
- Keep icon sizing proportional to text context and baseline alignment.
- Decorative icons should remain hidden from assistive tech where appropriate.

## jc-theme-switcher style mode switcher
- This repo currently standardizes on one active custom theme: `jc-theme-switcher`.
- Valid style schemes are `arcade` and `stripes`.
- Legacy `neon` should be treated as `stripes`.
- Default scheme is `stripes`.

- Data flow:
  - Server resolves initial scheme in `jc_16bit_arcade_get_style_scheme()` in [functions.php](../../themes/jc-theme-switcher/functions.php).
  - Header outputs body `data-style-scheme` and switcher buttons in [header.php](../../themes/jc-theme-switcher/header.php).
  - Client updates active mode and persistence in `applyStyleScheme()` in [theme.js](../../themes/jc-theme-switcher/assets/js/theme.js).

- Persistence contract:
  - Cookie key is `jc_style_scheme`.
  - Do not change this key unless migration/back-compat is explicitly requested.

- Styling contract:
  - Keep mode-specific rules scoped under body scheme selectors:
    - `body[data-style-scheme="arcade"]`
    - `body[data-style-scheme="stripes"]`
  - Keep switcher control class contract intact:
    - `.jc-theme-switcher`
    - `.jc-theme-switcher__button`
    - `is-active`
    - `aria-pressed`
