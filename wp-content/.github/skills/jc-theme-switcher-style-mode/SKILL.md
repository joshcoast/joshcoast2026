---
name: jc-theme-switcher-style-mode
description: 'Reference and workflow for jc-theme-switcher style mode switching. Use when updating arcade/stripes behavior, switcher UI, body scheme attributes, cookie persistence, or mode-scoped CSS rules.'
argument-hint: 'Describe the switcher change you want (UI, persistence, default mode, new mode behavior, or CSS scoping).'
user-invocable: true
---

# jc-theme-switcher Style Mode Switcher

## When to use
- Updating or debugging style mode switching.
- Adjusting switcher markup, active states, or accessibility attributes.
- Changing scheme-specific CSS behavior for arcade or stripes.
- Auditing persistence behavior and back-compat.

## Current contract
- Valid schemes: `arcade`, `stripes`.
- Legacy alias: `neon` maps to `stripes`.
- Default scheme: `stripes`.
- Persistence key: `jc_style_scheme` cookie.

## Source of truth files
- [functions.php](../../../themes/jc-theme-switcher/functions.php)
- [header.php](../../../themes/jc-theme-switcher/header.php)
- [theme.js](../../../themes/jc-theme-switcher/assets/js/theme.js)
- [style.scss](../../../themes/jc-theme-switcher/src/styles/style.scss)
- [theme-arcade.scss](../../../themes/jc-theme-switcher/src/styles/theme-arcade.scss)
- [theme-stripes.scss](../../../themes/jc-theme-switcher/src/styles/theme-stripes.scss)

## Implementation map
1. Server-side scheme resolution:
- `jc_16bit_arcade_get_style_scheme()` reads cookie, sanitizes, normalizes legacy `neon`, defaults to `stripes`.

2. Initial render:
- Body includes `data-style-scheme="{scheme}"`.
- Body also has class `style-scheme-{scheme}`.
- Switcher buttons use `.jc-theme-switcher__button` + `data-style-scheme` + `is-active` + `aria-pressed`.

3. Client updates:
- `applyStyleScheme()` normalizes values, updates body dataset and classes, toggles active button state, writes/deletes `jc_style_scheme` cookie.

4. CSS scoping:
- Mode-specific styles are scoped to:
  - `body[data-style-scheme="arcade"]`
  - `body[data-style-scheme="stripes"]`

## Safe change checklist
- Keep scheme names and cookie key stable unless migration is explicitly requested.
- Preserve normalization for legacy `neon`.
- Keep `aria-pressed` in sync with `is-active`.
- Verify both modes in browser after changes.
- Rebuild theme assets when editing SCSS.

## Quick verification steps
1. Check body attributes/classes after mode toggle.
2. Confirm active switcher button and `aria-pressed` state.
3. Confirm cookie value updates to selected scheme.
4. Refresh and verify scheme persists.
5. Confirm no unscoped CSS rule leaks across modes.
