# Spec 04 — Orders Page (pages/orders.php)

## Goal

Form that places an order. Demonstrates the key cross-node write pattern:
verify on Node A → write to Node B → sync to Node C. Each step is visible.

## Design

This is the most architecturally important page. It must make the three-node
write sequence explicit — not hidden in a helper. Students should be able to
read the POST handler and see exactly which node each query hits.

Node C sync is best-effort (see domain-rules.md DR-03). If Node C is down,
show an inline warning but do not roll back the Node B insert.

## Implementation

**POST handler:**
```
Validate: user_id (non-empty, numeric, > 0)
Validate: item_name (non-empty)
Validate: quantity (numeric, >= 1)
Validate: price (numeric, > 0)

// Step 1: Verify user exists on Node A
try {
    $pdoA = getMariaDBConnection();
    $stmt = $pdoA->prepare("SELECT id, name FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = "User ID {$user_id} does not exist on Node A.";
        // Stop here — do not proceed to Node B
    }
} catch (PDOException $e) {
    $error = "Cannot verify user — Node A is unreachable: " . $e->getMessage();
    // Stop here
}

// Step 2: Insert order on Node B (only if Step 1 passed)
if (!$error) {
    try {
        $pdoB = getMySQLConnection();
        $stmt = $pdoB->prepare("INSERT INTO orders (user_id, item_name, quantity, price) VALUES (:uid, :item, :qty, :price)");
        $stmt->execute([...]);
        $orderId = $pdoB->lastInsertId();
        $message = "Order #{$orderId} placed on Node B (MySQL).";
    } catch (PDOException $e) {
        $error = "Cannot save order — Node B: " . $e->getMessage();
    }
}

// Step 3: Sync summary to Node C (best-effort)
if (!$error) {
    try {
        $pdoC = getPostgresConnection();
        $stmt = $pdoC->prepare("
            INSERT INTO order_summary (user_id, user_name, total_orders, total_spent)
            VALUES (:uid, :uname, 1, :price)
            ON CONFLICT (user_id) DO UPDATE
            SET total_orders = order_summary.total_orders + 1,
                total_spent  = order_summary.total_spent + EXCLUDED.total_spent,
                last_updated = CURRENT_TIMESTAMP
        ");
        $stmt->execute([':uid' => $user_id, ':uname' => $user['name'], ':price' => $price]);
        $message .= " Summary synced to Node C (PostgreSQL).";
    } catch (PDOException $e) {
        // Do NOT set $error — order is committed. Show a warning instead.
        $warning = "Order saved, but Node C sync failed: " . $e->getMessage();
    }
}
```

**HTML:**
- Node badge showing all three nodes (this page touches all 3)
- Success card (green) if `$message`
- Warning card (yellow/amber) if `$warning` (Node C down but order saved)
- Error card (red) if `$error`
- Form fields: user_id (number), item_name (text), quantity (number, min=1), price (number, step=0.01)
- Recent orders table from Node B (last 10, separate try/catch)
- Footer callout: explain the three-node flow and why Node C is best-effort

## Pages

- `pages/orders.php`

## DB Objects

- `node_a_users.users` — SELECT id, name WHERE id = :id
- `node_b_orders.orders` — INSERT (user_id, item_name, quantity, price)
- `node_b_orders.orders` — SELECT last 10 for table display
- `node_c_reports.order_summary` — INSERT ON CONFLICT DO UPDATE (UPSERT)

## Files Changed

- `pages/orders.php`

## Verify

- [ ] Empty form submission shows validation errors
- [ ] Non-existent user_id shows "User ID X does not exist on Node A."
- [ ] Valid submission creates row in Node B and syncs to Node C
- [ ] With Node C down: order still saved, amber warning shown, no rollback
- [ ] With Node A down: form shows error, no insert attempted on Node B
- [ ] Recent orders table refreshes after successful submission
- [ ] All echoed values go through htmlspecialchars()
- [ ] Three distinct DB steps are visible in the PHP code (not hidden in helpers)
- [ ] File is under 150 lines
