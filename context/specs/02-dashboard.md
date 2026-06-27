# Spec 02 — Dashboard (pages/index.php)

## Goal

A single page that queries all three nodes simultaneously and renders their
data in a three-column grid. Core teaching artefact: demonstrates independent
node failure.

## Design

Each node query is wrapped in its own try/catch. A failing node renders an
error card in its column; the other columns render normally. This is the
primary demonstration of distributed system partial failure.

Query limits: 5 rows each (dashboard is a summary, not a full listing).

## Implementation

**PHP block (top of file):**
```
require config, all 3 helpers

$users = []; $usersError = null;
$orders = []; $ordersError = null;
$summary = []; $summaryError = null;

try { $pdoA = getMariaDBConnection(); $users = query 5 recent } catch ...
try { $pdoB = getMySQLConnection(); $orders = query 5 recent } catch ...
try { $pdoC = getPostgresConnection(); $summary = query all ordered by total_spent DESC } catch ...
```

**HTML block:**
- Nav links to all pages + test_connections.php
- Three-column CSS grid (`grid-template-columns: repeat(3, 1fr)`)
- Each column: node badge (color-coded) → status dot → table OR error card
- Footer callout explaining the partial failure pattern

## Pages

- `pages/index.php`

## DB Objects

- `node_a_users.users` — SELECT id, name, student_id, created_at LIMIT 5 ORDER BY created_at DESC
- `node_b_orders.orders` — SELECT id, item_name, status, ordered_at LIMIT 5 ORDER BY ordered_at DESC
- `node_c_reports.order_summary` — SELECT user_name, total_orders, total_spent ORDER BY total_spent DESC

## API Endpoints

None — this is a plain PHP page.

## Files Changed

- `pages/index.php`

## Verify

- [ ] Page loads without PHP errors when all 3 nodes are up
- [ ] Stopping Node A's DB: only the left column shows error; other two load
- [ ] Stopping Node B's DB: only the middle column shows error
- [ ] Stopping Node C's DB: only the right column shows error
- [ ] All user-supplied DB values are passed through htmlspecialchars()
- [ ] Node badge colours match ui-context.md (blue/green/amber)
- [ ] File is under 150 lines
