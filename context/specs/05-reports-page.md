# Spec 05 — Reports Page (pages/reports.php)

## Goal

Read from all three nodes and merge the results in PHP. Demonstrates a
cross-node "join" — the application-level equivalent of a SQL JOIN across
separate servers.

## Design

The page fetches `order_summary` from Node C (pre-aggregated), then for
each row fetches the matching user's email from Node A, and the most recent
order from Node B. These three pieces of data are assembled in PHP and
rendered as one table.

This is intentionally verbose — students should see that what a SQL JOIN
does in one query requires explicit PHP loops when the data is on different servers.

An optimised alternative (using the denormalised `user_name` column) is
shown as a commented-out simpler path, so students understand the tradeoff.

## Implementation

**PHP block:**
```
// Fetch summary from Node C
try {
    $pdoC = getPostgresConnection();
    $summary = SELECT * FROM order_summary ORDER BY total_spent DESC
} catch → $summaryError

// For each summary row, enrich with Node A + Node B data
if (!$summaryError) {
    try { $pdoA = getMariaDBConnection(); } catch → $nodeAError
    try { $pdoB = getMySQLConnection(); } catch → $nodeBError

    foreach ($summary as &$row) {
        // Node A: get email
        if (!$nodeAError) {
            $stmt = $pdoA->prepare("SELECT email FROM users WHERE id = :id");
            $stmt->execute([':id' => $row['user_id']]);
            $userRow = $stmt->fetch();
            $row['email'] = $userRow ? $userRow['email'] : '(Node A unavailable)';
        } else {
            $row['email'] = '(Node A unavailable)';
        }

        // Node B: get most recent order
        if (!$nodeBError) {
            $stmt = $pdoB->prepare("SELECT item_name, ordered_at FROM orders WHERE user_id = :id ORDER BY ordered_at DESC LIMIT 1");
            $stmt->execute([':id' => $row['user_id']]);
            $orderRow = $stmt->fetch();
            $row['last_item'] = $orderRow ? $orderRow['item_name'] : 'No orders';
            $row['last_order_at'] = $orderRow ? $orderRow['ordered_at'] : '-';
        } else {
            $row['last_item'] = '(Node B unavailable)';
        }
    }
}
```

**HTML:**
- Three node badges (all three are touched)
- Error card if $summaryError (Node C required; nothing to show without it)
- Node A / Node B warnings inline in table cells if those nodes are down
- Table columns: User Name | Email (Node A) | Total Orders | Total Spent | Last Item (Node B) | Last Order Date
- Footer callout: explain that this is an app-level join, why it's necessary,
  and what the tradeoff is vs. the denormalised `user_name` shortcut

## Pages

- `pages/reports.php`

## DB Objects

- `node_c_reports.order_summary` — SELECT * ORDER BY total_spent DESC
- `node_a_users.users` — SELECT email WHERE id = :id (per row)
- `node_b_orders.orders` — SELECT item_name, ordered_at WHERE user_id = :id LIMIT 1 (per row)

## Files Changed

- `pages/reports.php`

## Verify

- [ ] Page loads with data from all 3 nodes when all are up
- [ ] With Node A down: email column shows "(Node A unavailable)" for all rows; rest renders
- [ ] With Node B down: last item column shows "(Node B unavailable)"; rest renders
- [ ] With Node C down: page shows "Cannot load summary — Node C is required for this page."
- [ ] Table is readable and correctly maps columns across nodes by user_id
- [ ] Teaching callout explains the app-level join pattern
- [ ] All echoed values go through htmlspecialchars()
- [ ] File is under 150 lines
