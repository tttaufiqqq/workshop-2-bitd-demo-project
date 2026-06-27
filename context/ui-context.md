# UI Context — Distributed DB Demo

## References

- No external CSS framework
- No JavaScript (except optional inline for UX)
- All styles are inline `<style>` blocks per page
- Design must be readable on any browser without internet access

## Design Tokens

```css
/* Node colour system — used consistently across all pages */
--node-a-bg:   #dbeafe;   --node-a-text: #1e40af;  /* Blue  — MariaDB  */
--node-b-bg:   #dcfce7;   --node-b-text: #166534;  /* Green — MySQL    */
--node-c-bg:   #fef3c7;   --node-c-text: #92400e;  /* Amber — Postgres */

/* Status colours */
--ok-bg:    #f0fdf4;  --ok-text:    #166534;
--error-bg: #fef2f2;  --error-text: #dc2626;

/* Neutral */
--body-bg:  #f5f5f5;
--card-bg:  #ffffff;
--border:   #eeeeee;
--muted:    #888888;

/* Typography */
--font-body: sans-serif;
--font-mono: monospace;
```

## Page Hierarchy

```
index.php         ← Dashboard (all 3 nodes visible at once)
├── register.php  ← Node A write (users)
├── orders.php    ← Node B write (orders) + Node A read (verify user)
└── reports.php   ← Node C read + Node A + B reads (cross-node join)

setup/
└── test_connections.php  ← first page students open
```

## Layout Structure

All pages use a **single-column max-width layout** (`max-width: 1100px` for
dashboard, `max-width: 700px` for form pages).

No sidebar. No navigation component file. Each page has its own inline `<nav>`
block with plain `<a>` links.

### Dashboard Layout (index.php)

```
┌─────────────────────────────────────────────────────────┐
│  h1: APP_NAME — Dashboard                               │
│  nav: [Dashboard] [Register] [Orders] [Reports] [Test]  │
│                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │ Node A      │  │ Node B      │  │ Node C      │    │
│  │ MariaDB     │  │ MySQL       │  │ PostgreSQL  │    │
│  │ [badge]     │  │ [badge]     │  │ [badge]     │    │
│  │ ● status    │  │ ● status    │  │ ● status    │    │
│  │ table/error │  │ table/error │  │ table/error │    │
│  └─────────────┘  └─────────────┘  └─────────────┘    │
│                                                         │
│  footer note (teaching callout)                         │
└─────────────────────────────────────────────────────────┘
```

### Form Page Layout (register.php, orders.php)

```
┌───────────────────────────────────────┐
│  ← Dashboard                          │
│  [node badge]                         │
│  h1: Page title                       │
│  [success message]  or  [error msg]   │
│  <form>                               │
│    label + input (stacked)            │
│    [Submit button]                    │
│  </form>                              │
│  h2: Current records (live table)     │
└───────────────────────────────────────┘
```

## Component Patterns

### Node Badge
```html
<span class="node-tag node-a">Node A — MariaDB</span>
<span class="node-tag node-b">Node B — MySQL</span>
<span class="node-tag node-c">Node C — PostgreSQL</span>
```

### Status Dot
```html
<span class="status-dot dot-ok"></span>   <!-- green -->
<span class="status-dot dot-err"></span>  <!-- red -->
```

### Error Card
```html
<div class="error">❌ <?= htmlspecialchars($errorMessage) ?></div>
```

### Success Card
```html
<div class="ok">✅ <?= htmlspecialchars($successMessage) ?></div>
```

### Teaching Callout (footer of each page)
Every page ends with a `<p class="callout">` that explains in plain English
what distributed DB concept the page demonstrates. Example:

```html
<p class="callout">
  This page reads from Node A (MariaDB) to verify the user exists, then
  writes to Node B (MySQL). These are separate servers — there is no
  foreign key enforcing the relationship. The PHP code is the integrity layer.
</p>
```

## Modal Usage

No modals in this demo. Confirmations use inline messages only.

## Mobile

Not a priority for this demo. A readable desktop layout is sufficient.
Students will be viewing this on lab computers.
