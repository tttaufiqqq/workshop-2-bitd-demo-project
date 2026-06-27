<?php
// pages/index.php — Dashboard
// Queries all 3 nodes independently. A failed node shows an error card
// in its column only — the other columns still render. This is the
// core distributed systems teaching moment.

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_mariadb.php';
require_once __DIR__ . '/../includes/db_mysql.php';
require_once __DIR__ . '/../includes/db_postgres.php';
require_once __DIR__ . '/../includes/styles.php';

$users = []; $usersError = null;
$orders = []; $ordersError = null;
$summary = []; $summaryError = null;

try {
    $pdoA  = getMariaDBConnection();
    $users = $pdoA->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { $usersError = $e->getMessage(); }

try {
    $pdoB   = getMySQLConnection();
    $orders = $pdoB->query("SELECT * FROM orders ORDER BY ordered_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { $ordersError = $e->getMessage(); }

try {
    $pdoC    = getPostgresConnection();
    $summary = $pdoC->query("SELECT * FROM order_summary ORDER BY total_spent DESC")->fetchAll();
} catch (Exception $e) { $summaryError = $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — <?= APP_NAME ?></title>
    <?php renderStyles(); ?>
</head>
<body>
<?php renderNav('dashboard'); ?>
<div class="page">

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Live data from all three nodes — each column queries a separate database on a separate machine.</p>
</div>

<div class="grid-3">

    <div class="card">
        <div class="card-label">Node A &middot; <?= DB_A_HOST ?></div>
        <div class="card-title">
            <span class="status-dot <?= $usersError ? 'dot-err' : 'dot-ok' ?>"></span>
            <span class="badge badge-a">MariaDB</span> Users
        </div>
        <?php if ($usersError): ?>
            <div class="alert alert-err">❌ <?= htmlspecialchars($usersError) ?></div>
        <?php elseif (empty($users)): ?>
            <p class="empty">No users yet. <a href="register.php">Register one →</a></p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>Name</th><th>Student ID</th></tr>
                <?php foreach ($users as $u): ?>
                <tr><td><?= $u['id'] ?></td><td><?= htmlspecialchars($u['name']) ?></td><td><?= htmlspecialchars($u['student_id']) ?></td></tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-label">Node B &middot; <?= DB_B_HOST ?></div>
        <div class="card-title">
            <span class="status-dot <?= $ordersError ? 'dot-err' : 'dot-ok' ?>"></span>
            <span class="badge badge-b">MySQL</span> Orders
        </div>
        <?php if ($ordersError): ?>
            <div class="alert alert-err">❌ <?= htmlspecialchars($ordersError) ?></div>
        <?php elseif (empty($orders)): ?>
            <p class="empty">No orders yet. <a href="orders.php">Place one →</a></p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>Item</th><th>Status</th></tr>
                <?php foreach ($orders as $o): ?>
                <tr><td><?= $o['id'] ?></td><td><?= htmlspecialchars($o['item_name']) ?></td><td><?= htmlspecialchars($o['status']) ?></td></tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-label">Node C &middot; <?= DB_C_HOST ?></div>
        <div class="card-title">
            <span class="status-dot <?= $summaryError ? 'dot-err' : 'dot-ok' ?>"></span>
            <span class="badge badge-c">PostgreSQL</span> Reports
        </div>
        <?php if ($summaryError): ?>
            <div class="alert alert-err">❌ <?= htmlspecialchars($summaryError) ?></div>
        <?php elseif (empty($summary)): ?>
            <p class="empty">No report data yet.</p>
        <?php else: ?>
            <table>
                <tr><th>User</th><th>Orders</th><th>Spent</th></tr>
                <?php foreach ($summary as $s): ?>
                <tr><td><?= htmlspecialchars($s['user_name']) ?></td><td><?= $s['total_orders'] ?></td><td>RM <?= number_format($s['total_spent'], 2) ?></td></tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

</div>

<div class="callout">
    Each column above queries a <strong>different database on a different machine</strong> via Tailscale.
    If a node is down, only that column shows an error — the others still work. This is distributed fault tolerance in practice.
</div>

</div>
</body>
</html>
