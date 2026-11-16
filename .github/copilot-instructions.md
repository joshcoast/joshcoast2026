# Copilot Instructions — JoshCoast 2026

The project instructions live in `AGENTS.md` at the repo root (VS Code Copilot loads it automatically). Read and follow `AGENTS.md` for the project overview, Local app setup, build, deployment, commands, and gotchas.

Key points, in case `AGENTS.md` isn't loaded:

- WordPress 7.x. Only custom code under `wp-content/` is tracked; core and `wp-config.php` are not in the repo.
- Local development uses the Local app. `/Users/Josh.Coast/Local Sites/joshcoast2026/app/public/wp-content` is a symlink to this repo's `wp-content/`, so edits here are live at `http://joshcoast2026.local/`.
- Build with `npm run build` in `coast-blocks`, `toolbox-blocks`, and `jc-theme-switcher`.
- Pushing to `main` deploys to Nexcess via `.github/workflows/deploy.yml`.
- Never run `npx wp-scripts lint-js --fix <path>`; use `npx prettier --write <file>`.
