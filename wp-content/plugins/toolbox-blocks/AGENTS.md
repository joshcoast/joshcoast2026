# AGENTS.md – Toolbox Blocks

## Overview

**Toolbox Blocks** is a WordPress block plugin providing a suite of blocks with full responsive style controls. Blocks: Container, Grid, Text, Headline, Button, Image, Query.

- **PHP:** Server-side rendering via `render_callback`; blocks extend `Toolbox_Block_Base`.
- **JS:** React editor UI built with `@wordpress/scripts` (webpack); entry point `src/index.js`.
- **Styles:** Shared `StylesPanel` drives responsive CSS (desktop/tablet/mobile + main/hover); PHP `Toolbox_Blocks_CSS_Generator` outputs inline `<style>` per block.

---

## Conventions (Must Follow)

### 1. Mobile First

- **Default:** Design and implement mobile-first (base styles for small screens, then enhance for larger screens).
- **Exception:** The **screen size / device switcher** UI (Desktop | Tablet | Mobile) and its underlying styles object (`styles.desktop`, `styles.tablet`, `styles.mobile`) are **desktop-first**. The editor lets users set values per viewport; the CSS generator uses `max-width` media queries for tablet and mobile.

### 2. BEM (CSS & HTML)

- Use BEM naming for block/element/modifier where possible:
  - **Block:** `.tb-container`, `.tb-button`
  - **Element:** `.tb-container__inner`, `.tb-device-switcher__btn`
  - **Modifier:** `.tb-device-switcher__btn--active` or state classes like `.is-active`
- Prefix all custom classes with `tb-` to avoid collisions.
- Apply BEM in both `editor.css` and PHP-rendered HTML.

### 3. Security

- **PHP:** Escape all output: `esc_attr()`, `esc_url()`, `wp_kses_post()` (allowed HTML). Sanitize input: `sanitize_html_class()`, `sanitize_key()`, `absint()`, `tag_escape()`.
- **URLs:** Always use `esc_url()` for `href` and `src`.
- **User content:** Use `wp_kses_post()` or appropriate `wp_kses_*` for HTML; never output raw user input.
- **Block attributes:** Validate and sanitize before use (e.g. `in_array()` for enums, `absint()` for numbers).

### 4. W3C Accessibility

- Semantic HTML: use `<section>`, `<article>`, `<nav>`, etc. when appropriate.
- Images: require and expose `alt`; support captions via `<figcaption>`.
- Links: for `target="_blank"`, include `rel="noopener noreferrer"`.
- Interactive UI: use `role="tablist"`, `role="tab"`, `aria-selected`, `aria-label` as needed; ensure keyboard operability.
- Color contrast and focus indicators must meet WCAG 2.1 requirements.
- Avoid ARIA when native HTML is sufficient; prefer `button` over `div` for clickable controls.

### 5. WordPress PHP Standards

- Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
- Use `@package ToolboxBlocks` in file headers.
- Guard all PHP files: `if ( ! defined( 'ABSPATH' ) ) { exit; }`
- Use `__()` / `_e()` with text domain `toolbox-blocks` for translatable strings.
- Prefer `register_block_type()` with `render_callback` over `save` for dynamic blocks.

---

## Architecture

### File Structure

```
toolbox-blocks/
├── toolbox-blocks.php          # Main plugin file, registration
├── includes/
│   ├── class-block-base.php    # Abstract base for block render
│   ├── class-css-generator.php # PHP CSS generation (breakpoints, hover)
│   └── blocks/
│       └── class-*.php         # One file per block (Container, Grid, etc.)
├── src/
│   ├── index.js                # Entry point, imports all blocks
│   ├── editor.css              # Editor-only styles (BEM)
│   ├── blocks/                 # One folder per block
│   │   └── {block}/
│   │       ├── index.js        # registerBlockType
│   │       ├── edit.js         # Edit component
│   │       └── settings.js     # Settings tab (optional)
│   ├── shared/
│   │   ├── StylesPanel.js      # Full style controls (Layout, Sizing, etc.)
│   │   ├── DeviceSwitcher.js   # Desktop/Tablet/Mobile tabs
│   │   ├── MainHoverTabs.js    # Main / Hover state tabs
│   │   └── InspectorTabs.js    # Settings | Styles tabs
│   ├── utils/
│   │   └── generate-css.js     # JS CSS generation for editor preview
│   └── hooks/
│       └── useEditorDevice.js  # Editor device state (desktop/tablet/mobile)
└── build/                      # Compiled assets (index.js, index.css)
```

### Breakpoints (CSS Generator)

- **Desktop:** no media query (base)
- **Tablet:** `max-width: 1024px`
- **Mobile:** `max-width: 767px`

### Common Block Attributes

- `uniqueId` – Stable ID for CSS scoping (e.g. `tb-{uniqueId}`)
- `styles` – Object: `desktop`, `desktopHover`, `tablet`, `tabletHover`, `mobile`, `mobileHover` (camelCase CSS props)
- `anchor` – HTML anchor id (from WordPress `supports.anchor`)
- `className` – Additional CSS classes (from WordPress `supports.customClassName`)

### Adding a New Block

1. Create `includes/blocks/class-{name}.php` extending `Toolbox_Block_Base`.
2. Create `src/blocks/{name}/` with `index.js` (registration) and `edit.js`.
3. Register in `toolbox-blocks.php` and add to the `$blocks` array.
4. Use `block_meta()` for class/style/anchor; use `Toolbox_Blocks_CSS_Generator::style_tag()` for inline CSS.

---

## Build

```bash
npm install
npm run build   # Production
npm run start   # Watch mode
```

---

## Key Gotchas

- **Inline CSS:** Blocks output per-block `<style>` tags; no global stylesheet for dynamic styles.
- **Block save:** Dynamic blocks use `save: () => null` or minimal save; PHP renders the final HTML.
- **Editor device:** `useEditorDevice` syncs with WordPress preview device; StylesPanel values are stored per device.
- **Unique ID:** Generated from `clientId` (letters/numbers, truncated); used for `.tb-{uniqueId}` selectors.
