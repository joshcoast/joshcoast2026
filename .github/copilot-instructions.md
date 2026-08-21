# Copilot Instructions — JoshCoast 2026

## Project Overview

WordPress 6.9.x website. The repo tracks only custom themes/plugins under `wp-content/`;
WordPress core and `wp-config.php` are `.gitignore`-d and installed separately via WP-CLI.

## Stack

- **CMS:** WordPress 6.9.x
- **Language:** PHP 8.3
- **Database:** MariaDB 10.11
- **Web Server:** Apache 2 with `mod_rewrite`
- **CLI:** WP-CLI 2.12+

## Key Commands

| Task | Command |
|---|---|
| PHP lint | `php -l <file.php>` |
| DB health check | `wp db check` |
| List posts | `wp post list` |
| Create post | `wp post create --post_title="Title" --post_content="Content" --post_status=publish` |
| WordPress version | `wp core version` |
| Update WordPress | `wp core update` |

Add `--allow-root` to any `wp` command when running as the root user (containerized envs).

## Build

Block plugins/themes with a `package.json` build via `npm run build` (wp-scripts):

- `wp-content/plugins/coast-blocks`
- `wp-content/plugins/toolbox-blocks`
- `wp-content/themes/jc-theme-switcher`

## Deployment

`.github/workflows/deploy.yml` builds those packages and rsyncs a fixed allow-list of
`wp-content/themes/*` and `wp-content/plugins/*` directories to Nexcess on push to `main`.
If you add a new custom theme or plugin that needs to ship, add it to that workflow's rsync list.

## Gotchas

- Only `wp-content/` custom code is tracked — do not add core files to git.
- `wp-content/` must be owned by `www-data` for uploads and plugin installs to work locally.
- Never run `npx wp-scripts lint-js --fix <path>` — it ignores the path and reformats the whole
  project. Use `npx prettier --write <file>` instead.

## Containerized / Cursor Cloud Setup

Start services before doing any work:

```bash
sudo service mariadb start
sudo service apache2 start
```

- Admin: `http://localhost/wp-admin/` — `admin` / `admin123`
- Apache DocumentRoot: `/etc/apache2/sites-available/wordpress.conf` → `/workspace`
