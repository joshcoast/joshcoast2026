---
description: Use the main JoshCoast 2026 checkout for local edits
---

Always make local code changes in `/Users/Josh.Coast/sites/joshcoast2026`, not in a generated sibling worktree such as `/Users/Josh.Coast/sites/joshcoast2026.worktrees/*`.

The Local app site (`/Users/Josh.Coast/Local Sites/joshcoast2026/app/public/wp-content`) is a symlink to this main checkout's `wp-content/`, so changes made in a worktree do not appear on the local site.

If a session starts inside a generated worktree, confirm whether the requested change needs to affect the local app. For local app work, port or apply changes directly to the main checkout and run any build commands from the corresponding package directory under `/Users/Josh.Coast/sites/joshcoast2026`.
