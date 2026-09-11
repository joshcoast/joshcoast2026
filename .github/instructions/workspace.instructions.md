---
description: Use the main JoshCoast 2026 checkout for local edits
---

Always make local code changes in `/Users/joshcoast/development/joshcoast2026`, not in a generated sibling worktree such as `/Users/joshcoast/development/joshcoast2026.worktrees/*`.

If a session starts inside a generated worktree, confirm whether the requested change needs to affect the local app. For local app work, port or apply changes directly to the main checkout and run any build commands from the corresponding package directory under `/Users/joshcoast/development/joshcoast2026`.
