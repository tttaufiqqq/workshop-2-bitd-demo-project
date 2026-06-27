<?php
// pages/orders.php
// Places an order. Three-node write pattern:
// Step 1: verify user on Node A (MariaDB)
// Step 2: insert order on Node B (MySQL)
// Step 3: sync summary to Node C (PostgreSQL) — best-effort

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_mariadb.php';
require_once __DIR__ . '/../includes/db_mysql.php';
require_once __DIR__ . '/../includes/db_postgres.php';
require_once __DIR__ . '/../includes/styles.php';

$message = null; $warning = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id   = trim($_POST['user_id']   ?? '');
    $item_name = trim($_POST['item_name'] ?? '');
    $quantity  = trim($_POST['quantity']  ?? '');
    $price     = trim($_POST['price']     ?? '');

    if (!is_numeric($user_id) || (int)$user_id <= 0 || empty($item_name) ||
        !is_numeric($quantity) || (int)$quantity < 1 ||
        !is_numeric($price) || (float)$price <= 0) {
        $error = "All fields are required. User ID and quantity must be positive integers; price must be > 0.";
    }

    $user = null;
    if (!$error) {
        // Step 1: Verify user exists on Node A (MariaDB)
        try {
            $pdoA = getMariaDBConnection();
            $stmt = $pdoA->prepare("SELECT id, name FROM users WHERE id = :id");
            $stmt->execute([':id' => (int)$user_id]);
            $user = $stmt->fetch();
            if (!$user) { $error = "User ID {$user_id} does not exist on Node A."; }
        } catch (PDOException $e) {
            $error = "Cannot verify user — Node A is unreachable: " . $e->getMessage();
        }
    }

    if (!$error) {
        // Step 2: Insert order on Node B (MySQL)
        try {
            $pdoB = getMySQLConnection();
            $stmt = $pdoB->prepare(
                "INSERT INTO orders (user_id, item_name, quantity, price) VALUES (:uid, :item, :qty, :price)"
            );
            $stmt->execute([':uid' => (int)$user_id, ':item' => $item_name,
                            ':qty' => (int)$quantity, ':price' => (float)$price]);
            $orderId = $pdoB->lastInsertId();
            $message = "Order #{$orderId} placed on Node B (MySQL).";
        } catch (PDOException $e) {
            $error = "Cannot save order — Node B: " . $e->getMessage();
        }
    }

    if (!$error) {
        // Step 3: Sync summary to Node C (PostgreSQL) — best-effort; do NOT roll back on failure
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
            $stmt->execute([':uid' => (int)$user_id, ':uname' => $user['name'], ':price' => (float)$price]);
            $message .= " Summary synced to Node C (PostgreSQL).";
        } catch (PDOException $e) {
            $warning = "Order saved, but Node C sync failed: " . $e->getMessage();
        }
    }
}

// Fetch recent orders from Node B (separate try/catch — independent of POST result)
$recentOrders = []; $recentError = null;
try {
    $pdoB         = getMySQLConnection();
    $recentOrders = $pdoB->query("SELECT * FROM orders ORDER BY ordered_at DESC LIMIT 10")->fetchAll();
} catch (Exception $e) { $recentError = "Cannot load recent orders — Node B: " . $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Place Order — <?= APP_NAME ?></title>
    <?php renderStyles(); ?>
</head>
<body>
<?php renderNav('orders'); ?>
<div class="page narrow">

<div class="page-header">
    <div class="badges"><span class="badge badge-a">Node A — MariaDB</span> <span class="badge badge-b">Node B — MySQL</span> <span class="badge badge-c">Node C — PostgreSQL</span></div>
    <h1>Place an Order</h1>
    <p>Verify on Node A &rarr; insert on Node B &rarr; sync to Node C. All three steps are explicit in the code.</p>
</div>

<?php if ($message): ?><div class="alert alert-ok">✅ <?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($warning): ?><div class="alert alert-warn">⚠️ <?= htmlspecialchars($warning) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-err">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card"><form method="POST">
    <div class="form-group">
        <label class="form-label">User ID <small>must exist on Node A</small></label>
        <input class="form-input" type="number" name="user_id" min="1" value="<?= htmlspecialchars($_POST['user_id'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label class="form-label">Item Name</label>
        <input class="form-input" type="text" name="item_name" value="<?= htmlspecialchars($_POST['item_name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label class="form-label">Quantity</label>
        <input class="form-input" type="number" name="quantity" min="1" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label class="form-label">Price <small>RM per unit</small></label>
        <input class="form-input" type="number" name="price" min="0.01" step="0.01" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
    </div>
    <button class="btn btn-primary" type="submit">Place Order →</button>
</form></div>

<h2 class="section-title">Recent Orders <small>Node B — MySQL</small></h2>
<?php if ($recentError): ?>
    <div class="alert alert-err"><?= htmlspecialchars($recentError) ?></div>
<?php elseif (empty($recentOrders)): ?>
    <p class="empty">No orders yet.</p>
<?php else: ?>
    <div class="card card-table"><table>
        <tr><th>ID</th><th>User</th><th>Item</th><th>Qty</th><th>Price</th><th>Ordered</th></tr>
        <?php foreach ($recentOrders as $o): ?>
        <tr><td><?= $o['id'] ?></td><td><?= $o['user_id'] ?></td><td><?= htmlspecialchars($o['item_name']) ?></td><td><?= $o['quantity'] ?></td><td>RM <?= number_format($o['price'], 2) ?></td><td><?= $o['ordered_at'] ?></td></tr>
        <?php endforeach; ?>
    </table></div>
<?php endif; ?>

<div class="callout">
    <strong>How this works:</strong> Step 1 queries Node A (MariaDB) to verify the user exists — if Node A is
    down, no order is attempted. Step 2 inserts into Node B (MySQL). Step 3 upserts the summary into Node C
    (PostgreSQL). Node C is <em>best-effort</em>: a failure there shows a warning but does not roll back the
    Node B insert.
</div>

</div>
</body>
</html>
