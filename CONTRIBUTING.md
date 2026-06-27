# Contributing — Distributed DB Demo

This guide is for team members extending or debugging the demo project.

---

## Before You Start

1. Run `setup/test_connections.php` — all three nodes must be green before
   writing any code.
2. Read `context/architecture.md` — especially the cross-node write flow and
   the NFR-to-architecture mapping.
3. Check `context/progress-tracker.md` for what's done and what's pending.

---

## Adding a New Page

1. Create `pages/your_page.php`
2. `require_once` only the config and helpers your page actually uses
3. Handle POST at the top, before any HTML output
4. Keep the file under 150 lines — extract helpers to `includes/` if needed
5. Follow the HTML structure in `context/ui-context.md`
6. Add a footer callout explaining what distributed DB concept the page shows
7. Add a nav link on `pages/index.php`
8. Update `context/progress-tracker.md`

---

## Modifying a DB Schema

1. Edit the relevant `db/node_{x}_{engine}.sql` file
2. Update `docs/database.md` to match
3. Re-run the SQL file on the affected node: `mysql -u root -p < db/node_a_mariadb.sql`
4. Update any PHP pages that query the changed table

---

## Code Rules (non-negotiable)

- Always use PDO prepared statements — no string interpolation in SQL
- Always use `htmlspecialchars()` on any value echoed to the browser
- Catch each node's failure independently — never let one node crash the whole page
- No Composer, no frameworks, no `.env` files

---

## Git Commit Format

```
type(scope): what changed and why (≤72 chars)

What was the problem?
What did you try?
What is the solution?
Files changed: list them here.
```

Types: `feat` (new page/feature), `fix` (bug), `docs` (docs only),
`refactor` (no behaviour change), `chore` (setup/config)

Example:
```
feat(orders): add order placement page with 3-node write flow

Students needed a page that shows the cross-node write pattern.
Tried putting the Node C sync in a helper but it hid the flow.
Solution: keep all 3 node writes inline in the POST handler
so students can read the sequence directly.
Files changed: pages/orders.php
```

---

## What Not to Do

- Don't add Composer or any external PHP libraries
- Don't create a `.env` file — use `config.php` only
- Don't add JavaScript frameworks or CDN CSS
- Don't abstract away the difference between MySQL and PostgreSQL
- Don't use `die()` on a node failure — show an inline error instead
- Don't commit `config.php` with real IPs or passwords
