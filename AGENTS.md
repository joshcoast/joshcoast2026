# AGENTS.md

Instructions for AI coding agents (Claude Code / Claude desktop, GitHub Copilot in VS Code) working in this repo. This file is the single source of truth; `CLAUDE.md` and `.github/copilot-instructions.md` point here.

## Project Overview

This is a WordPress 7.x website project ("JoshCoast 2026"). The repo tracks only custom themes/plugins under `wp-content/`; WordPress core and `wp-config.php` are not in the repo.

## Local Development

Local development uses the [Local](https://localwp.com/) app. The Local site's `wp-content` directory is a symlink to this repo's `wp-content/`:

```
/Users/Josh.Coast/Local Sites/joshcoast2026/app/public/wp-content -> /Users/Josh.Coast/sites/joshcoast2026/wp-content
```

- Edit files in this repo; changes are live on the Local site immediately.
- WordPress core and `wp-config.php` live in the Local site (`/Users/Josh.Coast/Local Sites/joshcoast2026/app/public/`), not in this repo.
- Site URL: `http://joshcoast2026.local/` (admin at `/wp-admin/`). The site must be started in the Local app first.
- Run WP-CLI through Local's "Open site shell" so it uses the site's PHP and database. `--allow-root` is not needed.
- The Local site's original `wp-content` was kept as `wp-content-original` next to the symlink.

### Local Stack

- **CMS:** WordPress 7.x
- **PHP:** 8.2
- **Database:** MySQL 8.4
- **Web Server:** nginx
- **Mail:** Mailpit (Local's email catcher)

## Build

Packages with a `package.json` build via `npm run build` from their own directory:

- `wp-content/plugins/coast-blocks`
- `wp-content/plugins/toolbox-blocks`
- `wp-content/themes/jc-theme-switcher` (SCSS sources in `src/styles/` compile to `assets/css/`)

## Deployment

`.github/workflows/deploy.yml` builds those packages and rsyncs a fixed allow-list of `wp-content/themes/*` and `wp-content/plugins/*` directories to Nexcess on push to `main`. If you add a new custom theme or plugin that needs to ship, add it to that workflow's rsync list.

## Key Commands

| Task | Command |
|---|---|
| PHP lint | `php -l <file.php>` |
| DB health check | `wp db check` |
| List posts | `wp post list` |
| WordPress version | `wp core version` |

## Gotchas

- Only `wp-content/` custom code is tracked. Do not add core files to git.
- Never run `npx wp-scripts lint-js --fix <path>`. It ignores the path and reformats the whole project. Use `npx prettier --write <file>` instead.
- After changing SCSS in `jc-theme-switcher`, rebuild (`npm run build`); the site loads the compiled `.min.css` files.
